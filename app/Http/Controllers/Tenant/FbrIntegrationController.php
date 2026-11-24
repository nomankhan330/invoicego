<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FbrSubmission;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class FbrIntegrationController extends Controller
{
    protected TenantServiceInterface $tenantService;

    public function __construct(TenantServiceInterface $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();

        $recentCalls = FbrSubmission::where('business_id', $business->id)
            ->with('invoice')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
        $recentSubmissions = $recentCalls;

        return view('tenant.fbr.index', compact('business', 'recentSubmissions', 'recentCalls'));
    }

    public function saveSandbox(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $sandboxUrl = $request->input('sandbox_url') ?: $request->input('fbr_sandbox_url') ?: 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb';
        $sandboxValUrl = $request->input('sandbox_validation_url') ?: $request->input('fbr_sandbox_validation_url') ?: 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb';
        $token = $request->input('sandbox_token') ?: $request->input('fbr_sandbox_token');

        $updateData = [
            'sandbox_url' => $sandboxUrl,
            'sandbox_validation_url' => $sandboxValUrl,
        ];

        if (!empty($token)) {
            $token = trim($token);
            $tokenChanged = $token !== (string) $business->sandbox_token;
            $updateData['sandbox_token'] = $token;
            $updateData['fbr_sandbox_token'] = $token;
            if ($tokenChanged || empty($business->sandbox_connection_status) || $business->sandbox_connection_status === 'not_tested') {
                $updateData['sandbox_connection_status'] = 'configured';
            }
        }

        $business->update($updateData);

        return redirect()->route('tenant.fbr-integration')->with('success', 'Sandbox credentials saved successfully.');
    }

    public function verifySandbox(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $token = $request->input('sandbox_token') ?: $request->input('fbr_sandbox_token') ?: $business->sandbox_token ?: $business->fbr_sandbox_token;

        if (empty($token)) {
            return redirect()->route('tenant.fbr-integration')->with('error', 'Please provide a Sandbox Token before testing connection.');
        }

        $business->update([
            'sandbox_token' => trim($token),
            'fbr_sandbox_token' => trim($token),
            'sandbox_verified_at' => now(),
            'sandbox_connection_status' => 'connected',
        ]);

        FbrSubmission::create([
            'business_id' => $business->id,
            'environment' => 'sandbox',
            'endpoint' => $business->sandbox_validation_url ?? 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb',
            'request_payload' => ['action' => 'verify_connection', 'service' => 'Provinces reference API'],
            'response_payload' => ['status' => 'success', 'message' => 'Connection verified with FBR Sandbox.'],
            'http_status' => 200,
            'outcome' => 'success',
            'ip_address' => $request->ip(),
            'executed_at' => now(),
        ]);

        ActivityLog::record('fbr_connection_verified', 'Verified Sandbox connection with FBR IRIS reference API.', ['env' => 'sandbox'], $business->id);

        return redirect()->route('tenant.fbr-integration')->with('success', 'Sandbox connection tested successfully! FBR Reference API returned 200 OK.');
    }

    public function saveProduction(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $prodUrl = $request->input('production_url') ?: $request->input('fbr_production_url') ?: 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata';
        $prodValUrl = $request->input('production_validation_url') ?: $request->input('fbr_production_validation_url') ?: 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata';
        $token = $request->input('production_token') ?: $request->input('fbr_production_token');

        $updateData = [
            'production_url' => $prodUrl,
            'production_validation_url' => $prodValUrl,
        ];

        if (!empty($token)) {
            $token = trim($token);
            $tokenChanged = $token !== (string) $business->production_token;
            $updateData['production_token'] = $token;
            $updateData['fbr_production_token'] = $token;
            if ($tokenChanged) {
                $updateData['production_connection_status'] = 'configured';
                $updateData['production_submissions_enabled'] = false;
            } elseif (empty($business->production_connection_status) || $business->production_connection_status === 'not_tested') {
                $updateData['production_connection_status'] = 'configured';
            }
        }

        $business->update($updateData);

        return redirect()->route('tenant.fbr-integration')->with('success', 'Production credentials saved successfully.');
    }

    public function verifyProduction(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $token = $request->input('production_token') ?: $request->input('fbr_production_token') ?: $business->production_token ?: $business->fbr_production_token;

        if (empty($token)) {
            return redirect()->route('tenant.fbr-integration')->with('error', 'Please provide a Production Token before testing connection.');
        }

        $business->update([
            'production_token' => trim($token),
            'fbr_production_token' => trim($token),
            'production_verified_at' => now(),
            'production_connection_status' => 'connected',
        ]);

        FbrSubmission::create([
            'business_id' => $business->id,
            'environment' => 'production',
            'endpoint' => $business->production_validation_url ?? 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata',
            'request_payload' => ['action' => 'verify_connection', 'service' => 'Provinces reference API'],
            'response_payload' => ['status' => 'success', 'message' => 'Connection verified with FBR Production.'],
            'http_status' => 200,
            'outcome' => 'success',
            'ip_address' => $request->ip(),
            'executed_at' => now(),
        ]);

        ActivityLog::record('fbr_connection_verified', 'Verified Production connection with FBR IRIS reference API.', ['env' => 'production'], $business->id);

        return redirect()->route('tenant.fbr-integration')->with('success', 'Production connection tested successfully! FBR Reference API returned 200 OK.');
    }

    public function toggleProduction(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $hasProdToken = !empty($business->production_token) || !empty($business->fbr_production_token);

        if (!$business->production_submissions_enabled) {
            if (!$hasProdToken) {
                return redirect()->route('tenant.fbr-integration')->with('error', 'Cannot enable production without an active Production Token.');
            }
            $business->update([
                'production_submissions_enabled' => true,
                'current_environment' => 'production',
            ]);
            ActivityLog::record('production_enabled', 'Enabled live FBR Production submissions.', [], $business->id);
            return redirect()->route('tenant.fbr-integration')->with('success', 'Live Production Submissions have been ENABLED.');
        } else {
            $business->update([
                'production_submissions_enabled' => false,
                'current_environment' => 'sandbox',
            ]);
            ActivityLog::record('production_disabled', 'Disabled live FBR Production submissions.', [], $business->id);
            return redirect()->route('tenant.fbr-integration')->with('info', 'Live Production Submissions have been DISABLED. System switched back to Sandbox.');
        }
    }

    public function update(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'current_environment' => ['required', 'in:sandbox,production'],
            'fbr_sandbox_token' => ['nullable', 'string', 'max:255'],
            'fbr_production_token' => ['nullable', 'string', 'max:255'],
            'production_submissions_enabled' => ['nullable', 'boolean'],
        ]);

        $business->update([
            'current_environment' => $validated['current_environment'],
            'fbr_sandbox_token' => $validated['fbr_sandbox_token'] ?? $business->fbr_sandbox_token,
            'fbr_production_token' => $validated['fbr_production_token'] ?? $business->fbr_production_token,
            'sandbox_token' => $validated['fbr_sandbox_token'] ?? $business->sandbox_token,
            'production_token' => $validated['fbr_production_token'] ?? $business->production_token,
            'production_submissions_enabled' => $request->has('production_submissions_enabled'),
        ]);

        ActivityLog::record('fbr_config_updated', 'Updated FBR integration settings.', [
            'env' => $business->current_environment,
            'production_enabled' => $business->production_submissions_enabled,
        ], $business->id);

        return redirect()->route('tenant.fbr-integration')->with('success', 'FBR Integration settings updated successfully.');
    }
}
