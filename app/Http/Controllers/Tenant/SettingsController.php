<?php

namespace App\Http\Controllers\Tenant;

use App\Filament\Resources\BusinessResource;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SandboxScenario;
use App\Services\Contracts\TenantServiceInterface;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected TwoFactorService $twoFactorService
    ) {}

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $provinces = array_values(BusinessResource::provincesOptions());
        $businessNatures = array_values(BusinessResource::businessNaturesOptions());
        $sectors = array_values(BusinessResource::sectorsOptions());

        $selectedSectors = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($business->sector ?? ''))
        )));

        $selectedNatures = is_array($business->business_natures)
            ? array_values(array_filter($business->business_natures))
            : [];

        $invoiceQuota = $business->getInvoiceQuotaSummary();
        $totalScenarios = SandboxScenario::count();
        $passedScenarios = $business->sandboxScenarios()->wherePivot('status', 'passed')->count();
        $currentPublicIp = ActivityLog::resolveClientIp();

        $recentActivities = ActivityLog::withoutGlobalScopes()
            ->where(function ($query) use ($business, $user) {
                $query->where('business_id', $business->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->whereNull('business_id')->where('user_id', $user->id);
                    });
            })
            ->latest('id')
            ->limit(15)
            ->get();

        // Two-factor authentication state
        $twoFactorEnabled = $user->hasTwoFactorEnabled();
        $pendingTwoFactorSecret = !$twoFactorEnabled ? $request->session()->get('two_factor:pending_secret') : null;
        $twoFactorQrUri = $pendingTwoFactorSecret
            ? $this->twoFactorService->getProvisioningUri('InvoiceGO', $user->email, $pendingTwoFactorSecret)
            : null;
        $twoFactorFormattedSecret = $pendingTwoFactorSecret
            ? $this->twoFactorService->formatSecretForDisplay($pendingTwoFactorSecret)
            : null;
        $freshRecoveryCodes = $request->session()->get('two_factor_recovery_codes_display');

        $securityErrorKeys = ['owner_name', 'current_password', 'new_password', 'totp_code', 'disable_password'];
        $hasSecurityErrors = false;
        if ($request->session()->has('errors')) {
            $errorBag = $request->session()->get('errors');
            foreach ($securityErrorKeys as $key) {
                if ($errorBag->has($key)) {
                    $hasSecurityErrors = true;
                    break;
                }
            }
        }

        $activeTab = $request->query('tab') === 'security'
            || session('settings_tab') === 'security'
            || $pendingTwoFactorSecret !== null
            || $freshRecoveryCodes !== null
            || $hasSecurityErrors
            ? 'security'
            : 'profile';

        return view('tenant.settings.index', compact(
            'business',
            'user',
            'provinces',
            'businessNatures',
            'sectors',
            'selectedSectors',
            'selectedNatures',
            'invoiceQuota',
            'totalScenarios',
            'passedScenarios',
            'currentPublicIp',
            'recentActivities',
            'twoFactorEnabled',
            'pendingTwoFactorSecret',
            'twoFactorQrUri',
            'twoFactorFormattedSecret',
            'freshRecoveryCodes',
            'activeTab'
        ));
    }

    public function update(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $isFullForm = $request->boolean('profile_form');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'strn' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'business_natures' => $isFullForm ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'business_natures.*' => ['string', 'max:100'],
            'sector' => $isFullForm ? ['required'] : ['nullable'],
        ], [
            'business_natures.required' => 'Please select at least one Business Nature.',
            'sector.required' => 'Please select at least one Sector.',
        ]);

        $updatePayload = [
            'name' => trim($validated['name']),
            'strn' => isset($validated['strn']) && trim((string) $validated['strn']) !== '' ? trim((string) $validated['strn']) : null,
            'address' => trim($validated['address']),
            'province' => trim($validated['province']),
        ];

        if ($request->has('city')) {
            $updatePayload['city'] = trim((string) ($validated['city'] ?? '')) ?: null;
        }

        if ($request->has('business_natures')) {
            $updatePayload['business_natures'] = array_values(array_unique(array_filter(
                array_map('trim', (array) $request->input('business_natures', []))
            )));
        }

        if ($request->has('sector')) {
            $rawSector = $request->input('sector');
            if (is_array($rawSector)) {
                $sectorsClean = array_values(array_unique(array_filter(array_map('trim', $rawSector))));
                $updatePayload['sector'] = implode(', ', $sectorsClean);
            } else {
                $updatePayload['sector'] = trim((string) $rawSector);
            }
        }

        $business->update($updatePayload);

        if ($request->has('phone') && $user) {
            $user->update([
                'phone' => trim((string) ($validated['phone'] ?? '')) ?: null,
            ]);
        }

        ActivityLog::record(
            'business_profile_updated',
            "Updated business profile for '{$business->name}'.",
            [
                'business_name' => $business->name,
                'province' => $business->province,
                'city' => $business->city,
                'sector' => $business->sector,
                'business_natures' => $business->business_natures,
            ],
            $business->id
        );

        return redirect()
            ->route('tenant.settings')
            ->with('success', 'Business profile updated successfully.');
    }

    public function updateAccount(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_cnic' => ['nullable', 'string', 'max:30'],
            'current_password' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $passwordChanged = false;
        $userUpdate = [
            'name' => trim($validated['owner_name']),
            'phone' => trim((string) ($validated['owner_phone'] ?? '')) ?: null,
            'cnic' => trim((string) ($validated['owner_cnic'] ?? '')) ?: null,
        ];

        if (!empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return redirect()
                    ->route('tenant.settings', ['tab' => 'security'])
                    ->withErrors(['current_password' => 'Your current password is incorrect.'])
                    ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            $userUpdate['password'] = Hash::make($validated['new_password']);
            $passwordChanged = true;
        }

        $user->update($userUpdate);

        ActivityLog::record(
            'account_security_updated',
            $passwordChanged
                ? "Updated account profile and changed password for {$user->email}."
                : "Updated account profile details for {$user->email}.",
            [
                'email' => $user->email,
                'password_changed' => $passwordChanged,
            ],
            $business->id
        );

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security')
            ->with('success', $passwordChanged
                ? 'Account details and password updated successfully.'
                : 'Account details updated successfully.');
    }

    /**
     * Step 1: Start Two-Factor Authentication setup by generating a fresh TOTP secret in session.
     */
    public function startTwoFactorSetup(Request $request)
    {
        $user = Auth::user();
        if ($user->hasTwoFactorEnabled()) {
            return redirect()
                ->route('tenant.settings', ['tab' => 'security'])
                ->with('settings_tab', 'security')
                ->with('success', 'Two-factor authentication is already enabled on your account.');
        }

        $secret = $this->twoFactorService->generateSecret(32);
        $request->session()->put('two_factor:pending_secret', $secret);

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security');
    }

    /**
     * Cancel pending 2FA setup.
     */
    public function cancelTwoFactorSetup(Request $request)
    {
        $request->session()->forget('two_factor:pending_secret');

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security');
    }

    /**
     * Step 2: Verify the 6-digit TOTP code and activate 2FA + generate 8 recovery codes.
     */
    public function confirmTwoFactor(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $pendingSecret = (string) $request->session()->get('two_factor:pending_secret', '');
        if ($pendingSecret === '') {
            return redirect()
                ->route('tenant.settings', ['tab' => 'security'])
                ->with('settings_tab', 'security')
                ->withErrors(['totp_code' => 'Two-factor setup session expired. Please start setup again.']);
        }

        $validated = $request->validate([
            'totp_code' => ['required', 'string', 'max:12'],
        ]);

        if (!$this->twoFactorService->verifyCode($pendingSecret, $validated['totp_code'])) {
            return redirect()
                ->route('tenant.settings', ['tab' => 'security'])
                ->with('settings_tab', 'security')
                ->withErrors(['totp_code' => 'The 6-digit authenticator code is invalid. Please check your app and try again.']);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes(8);

        $user->forceFill([
            'two_factor_secret' => $pendingSecret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor:pending_secret');

        ActivityLog::record(
            'two_factor_enabled',
            "Enabled Two-Factor Authentication (TOTP) for {$user->email}.",
            ['email' => $user->email],
            $business->id
        );

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security')
            ->with('two_factor_recovery_codes_display', $recoveryCodes)
            ->with('success', 'Two-factor authentication is now enabled. Save your recovery codes in a safe place.');
    }

    /**
     * Regenerate 8 fresh single-use recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('tenant.settings', ['tab' => 'security']);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes(8);
        $user->forceFill([
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        ActivityLog::record(
            'two_factor_recovery_regenerated',
            "Regenerated 2FA recovery codes for {$user->email}.",
            ['email' => $user->email],
            $business->id
        );

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security')
            ->with('two_factor_recovery_codes_display', $recoveryCodes)
            ->with('success', 'Fresh two-factor recovery codes generated. Previous codes are no longer valid.');
    }

    /**
     * Disable Two-Factor Authentication after verifying the user's current password.
     */
    public function disableTwoFactor(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $validated = $request->validate([
            'disable_password' => ['required', 'string'],
        ], [
            'disable_password.required' => 'Please enter your current password to disable two-factor authentication.',
        ]);

        if (!Hash::check($validated['disable_password'], $user->password)) {
            return redirect()
                ->route('tenant.settings', ['tab' => 'security'])
                ->with('settings_tab', 'security')
                ->withErrors(['disable_password' => 'Incorrect password. Two-factor authentication was not disabled.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget('two_factor:pending_secret');

        ActivityLog::record(
            'two_factor_disabled',
            "Disabled Two-Factor Authentication for {$user->email}.",
            ['email' => $user->email],
            $business->id
        );

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security')
            ->with('success', 'Two-factor authentication has been turned off.');
    }

    /**
     * Toggle Email Notifications preference (supports both AJAX JSON and form submission).
     */
    public function updateNotifications(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $user = Auth::user();

        $enabled = $request->boolean('email_notifications_enabled');

        $user->forceFill([
            'email_notifications_enabled' => $enabled,
        ])->save();

        ActivityLog::record(
            'email_notifications_updated',
            $enabled
                ? "Enabled email notifications for {$user->email}."
                : "Disabled email notifications for {$user->email}.",
            [
                'email' => $user->email,
                'email_notifications_enabled' => $enabled,
            ],
            $business->id
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'email_notifications_enabled' => $enabled,
                'message' => $enabled
                    ? 'Email notifications enabled.'
                    : 'Email notifications turned off.',
            ]);
        }

        return redirect()
            ->route('tenant.settings', ['tab' => 'security'])
            ->with('settings_tab', 'security')
            ->with('success', $enabled
                ? 'Email notification preferences enabled.'
                : 'Email notification preferences disabled.');
    }
}
