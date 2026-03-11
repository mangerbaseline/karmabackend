<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogContext
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->tenantContext->tenantId();
        // Avoid calling $request->user() here as it triggers DB connection 
        // before resolveTenant might have run for multitenant models.
        $rid = $request->headers->get('X-Request-Id');

        Log::withContext([
            'tenant_id' => $tenantId,
            'request_id' => $rid,
        ]);

        return $next($request);
    }
}
