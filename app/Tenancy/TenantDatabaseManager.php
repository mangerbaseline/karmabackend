<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;

class TenantDatabaseManager
{
    public function makeSlug(string $name): string
    {
        $slug = Str::slug($name);
        return $slug !== '' ? $slug : Str::lower(Str::random(8));
    }

    public function makeDbName(string $slug): string
    {
        $prefix = config('krema_tenancy.tenant_db_prefix', 'krema_tenant_');
        // MySQL database names have limits but 64 is safe enough.
        $candidate = $prefix . Str::replace('-', '_', Str::lower($slug));
        return substr($candidate, 0, 64);
    }

    /**
     * Create the tenant database (MySQL).
     */
    public function createDatabase(string $dbName): void
    {
        $this->validateDbIdentifier($dbName);

        if ($this->databaseExists($dbName)) {
            throw new RuntimeException("Tenant DB already exists: {$dbName}");
        }

        if (config('krema_tenancy.use_admin_connection', false)) {
            $pdo = $this->adminPdo();
            $pdo->exec('CREATE DATABASE ' . $this->quoteIdent($dbName) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            return;
        }

        // Use Laravel central connection
        DB::statement('CREATE DATABASE ' . $this->quoteIdent($dbName) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function databaseExists(string $dbName): bool
    {
        $this->validateDbIdentifier($dbName);

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        if (config('krema_tenancy.use_admin_connection', false)) {
            $pdo = $this->adminPdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dbName]);
            return (bool)$stmt->fetchColumn();
        }

        return (bool)DB::selectOne($sql, [$dbName]);
    }

    /**
     * Switch the 'tenant' connection to the given tenant DB and reconnect.
     */
    public function switchToTenantDb(string $dbName): void
    {
        $this->validateDbIdentifier($dbName);

        Config::set('database.connections.tenant.database', $dbName);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Run migrations against the tenant DB.
     */
    public function migrateTenant(string $dbName, bool $fresh = false): void
    {
        $this->switchToTenantDb($dbName);

        $path = config('krema_tenancy.tenant_migration_path', 'database/migrations');

        if ($fresh) {
            \Artisan::call('migrate:fresh', [
                '--database' => 'tenant',
                '--path' => $path,
                '--force' => true,
            ]);
            return;
        }

        \Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => $path,
            '--force' => true,
        ]);
    }

    private function adminPdo(): PDO
    {
        $cfg = config('krema_tenancy.admin');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $cfg['host'], $cfg['port'], $cfg['database']);
        return new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    private function validateDbIdentifier(string $dbName): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/', $dbName)) {
            throw new RuntimeException('Invalid database identifier.');
        }
    }

    private function quoteIdent(string $ident): string
    {
        // safe quote for mysql identifiers
        return '`' . str_replace('`', '``', $ident) . '`';
    }
}
