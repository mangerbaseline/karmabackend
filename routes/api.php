<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\TenantMembersController;

// Health
Route::get('/health', fn() => response()->json(['ok' => true]));

// Diagnostic: test key, DB, tenants
Route::get('/debug/system', function () {
    $results = [];

    // 1. Check APP_KEY
    $key = config('app.key');
    $results['app_key_set'] = !empty($key);
    $results['app_key_length'] = strlen($key ?? '');
    $results['app_key_starts_with_base64'] = str_starts_with($key ?? '', 'base64:');
    $results['cipher'] = config('app.cipher');

    // 2. Test encrypter
    try {
        $encrypted = encrypt('test');
        $decrypted = decrypt($encrypted);
        $results['encryption'] = $decrypted === 'test' ? 'OK' : 'MISMATCH';
    }
    catch (\Throwable $e) {
        $results['encryption'] = 'FAILED: ' . $e->getMessage();
    }

    // 3. Test DB connection (central)
    try {
        \Illuminate\Support\Facades\DB::connection('mysql')->getPdo();
        $results['db_central'] = 'OK';
    }
    catch (\Throwable $e) {
        $results['db_central'] = 'FAILED: ' . $e->getMessage();
    }

    // 4. Check tenants
    try {
        $tenants = \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('tenants')->get();
        $results['tenants'] = $tenants->toArray();
    }
    catch (\Throwable $e) {
        $results['tenants'] = 'FAILED: ' . $e->getMessage();
    }

    // 5. Check tenant_domains
    try {
        $domains = \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('tenant_domains')->get();
        $results['domains'] = $domains->toArray();
    }
    catch (\Throwable $e) {
        $results['domains'] = 'FAILED: ' . $e->getMessage();
    }

    // 6. Check tenant DB
    try {
        \Illuminate\Support\Facades\DB::connection('tenant')->getPdo();
        $results['db_tenant'] = 'OK';
    }
    catch (\Throwable $e) {
        $results['db_tenant'] = 'FAILED (expected before resolve): ' . $e->getMessage();
    }

    return response()->json($results);
});

// Modules (drop-in)
$modules = [
    __DIR__ . '/api.module1.php',
    __DIR__ . '/api.module2.php',
    __DIR__ . '/api.module3.php',
    __DIR__ . '/api.module4.php',
    __DIR__ . '/api.module5.php',
    __DIR__ . '/api.module6.php',
    __DIR__ . '/api.module7.php',
    __DIR__ . '/api.module8.php',
    __DIR__ . '/api.module9.php',
    __DIR__ . '/api.module11.php',
];

Route::middleware(['resolveTenant'])->group(function () use ($modules) {
    foreach ($modules as $f) {
        if (file_exists($f))
            require $f;
    }
});


// Identity + context
Route::middleware(['resolveTenant', 'auth:sanctum'])->group(function () {
    Route::get('/me', [MeController::class , 'me']);
    // Lists salons the current user can access within the current tenant
    Route::get('/me/salons', [MeController::class , 'salons'])->middleware(['requireTenantMembership']);
});

// Tenant membership management (central identity)
Route::middleware(['resolveTenant', 'auth:sanctum', 'requireTenantMembership'])->group(function () {
    Route::get('/tenant/members', [TenantMembersController::class , 'index']);
    Route::put('/tenant/members', [TenantMembersController::class , 'upsert']); // upsert by email
    Route::delete('/tenant/members/{userId}', [TenantMembersController::class , 'destroy']);
});


// Superadmin onboarding bootstrap (creates tenant + first owner + first salon)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/onboarding/bootstrap', [OnboardingController::class , 'bootstrap']);
});
