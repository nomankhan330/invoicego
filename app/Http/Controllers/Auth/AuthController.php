<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('tenant.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status === 'suspended') {
                Auth::logout();
                return back()->withErrors(['email' => 'Your user account has been suspended by administration.']);
            }

            // If user is super admin and visits /admin
            if ($user->is_super_admin && !$user->businesses()->exists()) {
                return redirect()->intended('/admin');
            }

            $business = $this->tenantService->getActiveBusiness();

            if ($business && $business->isPending()) {
                return redirect()->route('tenant.pending');
            }

            return redirect()->intended(route('tenant.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            // Owner details
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Auth::guest() ? 'unique:users,email' : 'nullable'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],

            // Business details
            'business_name' => ['required', 'string', 'max:255'],
            'ntn_cnic' => ['required', 'string', 'max:20', 'regex:/^\d{7}$|^\d{13}$/'],
            'strn' => ['nullable', 'string', 'max:50'],
            'business_address' => ['required', 'string'],
            'province' => ['required', 'string', 'max:100'],
            'sector' => ['required', 'string', 'max:150'],
            'business_natures' => ['required', 'array', 'min:1'],
            'is_retailer_registered' => ['nullable', 'boolean'],
        ], [
            'ntn_cnic.regex' => 'The NTN / CNIC must be either a 7-digit NTN or 13-digit CNIC containing digits only.',
            'password.min' => 'Password must be at least 10 characters with letters and numbers.',
            'business_natures.required' => 'Please select at least one business nature checkbox.',
        ]);

        $userData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $businessData = [
            'name' => $validated['business_name'],
            'ntn_cnic' => preg_replace('/\D/', '', $validated['ntn_cnic']),
            'strn' => $validated['strn'] ?? null,
            'address' => $validated['business_address'],
            'province' => $validated['province'],
            'sector' => $validated['sector'],
            'business_natures' => $validated['business_natures'],
            'is_retailer_registered' => $request->boolean('is_retailer_registered'),
        ];

        $business = $this->tenantService->registerBusiness($userData, $businessData);

        // Auto login owner
        if (!Auth::check()) {
            Auth::attempt([
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);
        }

        return redirect()->route('tenant.pending')->with('success', 'Business registered successfully! Super Admin approval is pending.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function switchBusiness(string $uuid)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $business = Business::where('uuid', $uuid)->firstOrFail();

        if (!$this->tenantService->canAccessBusiness($user, $business->id)) {
            abort(403, 'Unauthorized access to this business tenant.');
        }

        $this->tenantService->setActiveBusiness($business);

        if ($business->isPending()) {
            return redirect()->route('tenant.pending');
        }

        return redirect()->route('tenant.dashboard')->with('success', "Switched to '{$business->name}'");
    }

    public function pending()
    {
        $business = $this->tenantService->getActiveBusiness();
        if (!$business) {
            return redirect()->route('register');
        }

        if ($business->isApproved()) {
            return redirect()->route('tenant.dashboard');
        }

        return view('tenant.pending', compact('business'));
    }

    public function demoApprove()
    {
        $business = $this->tenantService->getActiveBusiness();
        if ($business && $business->isPending()) {
            $business->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            return redirect()->route('tenant.dashboard')->with('success', "Business '{$business->name}' has been approved! Welcome to your InvoiceGO Dashboard.");
        }

        return redirect()->route('tenant.dashboard');
    }

    public function rejected()
    {
        $business = $this->tenantService->getActiveBusiness();
        return view('tenant.rejected', compact('business'));
    }

    public function suspended()
    {
        $business = $this->tenantService->getActiveBusiness();
        return view('tenant.suspended', compact('business'));
    }
}

