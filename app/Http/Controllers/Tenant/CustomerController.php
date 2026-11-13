<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected FbrIntegrationServiceInterface $fbrService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();
        $customers = $business->customers()->latest()->paginate(15);

        return view('tenant.customers.index', compact('business', 'customers'));
    }

    public function create()
    {
        $business = $this->tenantService->getActiveBusiness();
        return view('tenant.customers.create', compact('business'));
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ntn_cnic' => ['required', 'string', 'max:20', 'regex:/^\d{7}$|^\d{13}$/'],
            'strn' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'province' => ['nullable', 'string', 'max:100'],
            'registration_type' => ['required', 'in:registered,unregistered'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'ntn_cnic.regex' => 'NTN / CNIC must be 7-digit NTN or 13-digit CNIC, digits only.',
        ]);

        $customer = $business->customers()->create($validated);

        // Auto verify buyer registration status with FBR
        $verification = $this->fbrService->checkBuyerRegistration($customer->ntn_cnic, $business);
        $customer->update(['fbr_verification_status' => 'verified']);

        return redirect()->route('tenant.customers.index')
            ->with('success', "Customer '{$customer->name}' created and verified with FBR.");
    }
}
