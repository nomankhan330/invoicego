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

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $query = $business->customers()->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ntn_cnic', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            if ($type === 'registered') {
                $query->where('registration_type', 'registered');
            } elseif ($type === 'unregistered') {
                $query->where('registration_type', 'unregistered');
            }
        }

        $customers = $query->paginate(15)->withQueryString();

        // Dynamic counter matching Screenshot 4: "0 registered · 0 unregistered buyers"
        $registeredCount = $business->customers()->where('registration_type', 'registered')->count();
        $unregisteredCount = $business->customers()->where('registration_type', 'unregistered')->count();

        $provinces = [
            'AZAD JAMMU AND KASHMIR' => 'AZAD JAMMU AND KASHMIR',
            'BALOCHISTAN' => 'BALOCHISTAN',
            'GILGIT-BALTISTAN' => 'GILGIT-BALTISTAN',
            'ISLAMABAD CAPITAL TERRITORY' => 'ISLAMABAD CAPITAL TERRITORY',
            'KHYBER PAKHTUNKHWA' => 'KHYBER PAKHTUNKHWA',
            'PUNJAB' => 'PUNJAB',
            'SINDH' => 'SINDH',
        ];

        return view('tenant.customers.index', compact('business', 'customers', 'registeredCount', 'unregisteredCount', 'provinces'));
    }

    public function create()
    {
        return redirect()->route('tenant.customers.index');
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_type' => ['required', 'in:registered,unregistered'],
            'ntn_cnic' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('registration_type') === 'registered' && empty($value)) {
                        $fail('The NTN / CNIC is required for registered buyers.');
                    }
                    if (!empty($value)) {
                        $clean = preg_replace('/\D/', '', $value);
                        if (strlen($clean) !== 7 && strlen($clean) !== 13) {
                            $fail('The NTN / CNIC must be 7 digits (NTN) or 13 digits (CNIC).');
                        }
                    }
                },
            ],
            'strn' => ['nullable', 'string', 'max:50'],
            'province' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = $business->customers()->create([
            'name' => $validated['name'],
            'registration_type' => $validated['registration_type'],
            'ntn_cnic' => !empty($validated['ntn_cnic']) ? preg_replace('/\D/', '', $validated['ntn_cnic']) : null,
            'strn' => $validated['strn'] ?? null,
            'province' => $validated['province'],
            'category' => $validated['category'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'fbr_verification_status' => 'verified',
        ]);

        return redirect()->route('tenant.customers.index')
            ->with('success', "Customer '{$customer->name}' added successfully.");
    }

    public function update(Request $request, $id)
    {
        $business = $this->tenantService->getActiveBusiness();
        $customer = $business->customers()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_type' => ['required', 'in:registered,unregistered'],
            'ntn_cnic' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('registration_type') === 'registered' && empty($value)) {
                        $fail('The NTN / CNIC is required for registered buyers.');
                    }
                    if (!empty($value)) {
                        $clean = preg_replace('/\D/', '', $value);
                        if (strlen($clean) !== 7 && strlen($clean) !== 13) {
                            $fail('The NTN / CNIC must be 7 digits (NTN) or 13 digits (CNIC).');
                        }
                    }
                },
            ],
            'strn' => ['nullable', 'string', 'max:50'],
            'province' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'registration_type' => $validated['registration_type'],
            'ntn_cnic' => !empty($validated['ntn_cnic']) ? preg_replace('/\D/', '', $validated['ntn_cnic']) : null,
            'strn' => $validated['strn'] ?? null,
            'province' => $validated['province'],
            'category' => $validated['category'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        return redirect()->route('tenant.customers.index')
            ->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $business = $this->tenantService->getActiveBusiness();
        $customer = $business->customers()->findOrFail($id);
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('tenant.customers.index')
            ->with('success', "Customer '{$name}' deleted.");
    }
}
