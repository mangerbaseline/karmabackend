<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private
        TenantContext $context, private
        TenantDatabaseManager $db
        )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        Log::info('[Tenancy] Resolving host: ' . $host);

        $domain = TenantDomain::with('tenant')
            ->where('host', $host)
            ->first();

        if (!$domain || !$domain->tenant) {
            Log::warning('[Tenancy] No tenant found for host: ' . $host);
            // If you want a public landing page, allow it here.
            return response()->json([
                'error' => 'TENANT_NOT_FOUND',
                'message' => 'Unknown tenant for host: ' . $host,
            ], 404);
        }

        if (($domain->tenant->status ?? 'active') !== 'active') {
            Log::warning('[Tenancy] Tenant suspended: ' . $domain->tenant->slug);
            return response()->json([
                'error' => 'TENANT_SUSPENDED',
                'message' => 'Tenant is suspended.',
            ], 403);
        }

        Log::info('[Tenancy] Resolved Tenant: ' . $domain->tenant->slug . ' (DB: ' . $domain->tenant->db_name . ')');

        $this->context->setTenant($domain->tenant);
        $this->db->switchToTenantDb($domain->tenant->db_name);

        // expose for controllers (optional)
        $request->attributes->set('currentTenant', $domain->tenant);

        return $next($request);
    }
}
