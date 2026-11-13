<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class FbrIntegrationController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();

        return view('tenant.fbr.index', compact('business'));
    }

    public function update(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'sandbox_token' => ['nullable', 'string'],
            'production_token' => ['nullable', 'string'],
            'current_environment' => ['required', 'in:sandbox,production'],
            'production_submissions_enabled' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'current_environment' => $validated['current_environment'],
            'production_submissions_enabled' => $request->boolean('production_submissions_enabled'),
        ];

        if (!empty($validated['sandbox_token'])) {
            $updateData['sandbox_token'] = trim($validated['sandbox_token']);
        }

        if (!empty($validated['production_token'])) {
            $updateData['production_token'] = trim($validated['production_token']);
        }

        $business->update($updateData);

        return back()->with('success', 'FBR Integration settings updated securely.');
    }
}

