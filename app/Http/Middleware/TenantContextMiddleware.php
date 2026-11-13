<?php

namespace App\Http\Middleware;

use App\Services\Contracts\TenantServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContextMiddleware
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $business = $this->tenantService->getActiveBusiness();

        if (!$business) {
            return redirect()->route('register')->with('info', 'Please register or select a business to proceed.');
        }

        // Allow access to status holding routes
        if ($request->routeIs('tenant.pending', 'tenant.rejected', 'tenant.suspended', 'switch.business', 'logout')) {
            return $next($request);
        }

        if ($business->isPending()) {
            return redirect()->route('tenant.pending');
        }

        if ($business->isRejected()) {
            return redirect()->route('tenant.rejected');
        }

        if ($business->isSuspended()) {
            return redirect()->route('tenant.suspended');
        }

        return $next($request);
    }
}
