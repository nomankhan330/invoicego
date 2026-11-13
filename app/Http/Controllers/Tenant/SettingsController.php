<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();

        return view('tenant.settings.index', compact('business'));
    }

    public function update(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'strn' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'province' => ['required', 'string', 'max:100'],
        ]);

        $business->update($validated);

        return back()->with('success', 'Business settings updated successfully.');
    }
}
