<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FbrSubmission;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class FbrIntegrationController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected FbrIntegrationServiceInterface $fbrService
    ) {}

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

        $sandboxUrl = $request->input('sandbox_url') ?: $request->input('fbr_sandbox_url') ?: config('services.fbr.sandbox_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb');
        $sandboxValUrl = $request->input('sandbox_validation_url') ?: $request->input('fbr_sandbox_validation_url') ?: config('services.fbr.sandbox_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb');
        $token = $request->input('sandbox_token') ?: $request->input('fbr_sandbox_token');

        $updateData = [
            'sandbox_url' => trim($sandboxUrl),
            'sandbox_validation_url' => trim($sandboxValUrl),
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
        $sandboxUrl = $request->input('sandbox_url') ?: $request->input('fbr_sandbox_url') ?: $business->sandbox_url ?: config('services.fbr.sandbox_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb');
        $sandboxValUrl = $request->input('sandbox_validation_url') ?: $request->input('fbr_sandbox_validation_url') ?: $business->sandbox_validation_url ?: config('services.fbr.sandbox_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb');
        $token = $request->input('sandbox_token') ?: $request->input('fbr_sandbox_token') ?: $business->sandbox_token ?: $business->fbr_sandbox_token;

        if (empty($token)) {
            return redirect()->route('tenant.fbr-integration')->with('error', 'Please provide a Sandbox Token before testing connection.');
        }

        $token = trim($token);

        // Perform live FBR Reference API verification
        $result = $this->fbrService->verifyConnection($business, 'sandbox', $token);

        $business->update([
            'sandbox_url' => trim($sandboxUrl),
            'sandbox_validation_url' => trim($sandboxValUrl),
            'sandbox_token' => $token,
            'fbr_sandbox_token' => $token,
            'sandbox_verified_at' => now(),
            'sandbox_connection_status' => $result['success'] ? 'connected' : 'failed',
        ]);

        FbrSubmission::create([
            'business_id' => $business->id,
            'environment' => 'sandbox',
            'endpoint' => $result['endpoint'],
            'request_payload' => ['action' => 'verify_connection', 'service' => 'FBR Provinces Reference API'],
            'response_payload' => $result['response'],
            'http_status' => $result['http_status'] ?: 500,
            'outcome' => $result['success'] ? 'success' : ($result['http_status'] === 0 ? 'network_error' : 'rejected'),
            'error_message' => $result['success'] ? null : implode('; ', $result['errors'] ?: [$result['message']]),
            'ip_address' => $request->ip(),
            'executed_at' => now(),
        ]);

        ActivityLog::record(
            $result['success'] ? 'fbr_connection_verified' : 'fbr_connection_failed',
            $result['message'],
            ['env' => 'sandbox', 'http_status' => $result['http_status']],
            $business->id
        );

        if ($result['success']) {
            return redirect()->route('tenant.fbr-integration')->with('success', $result['message']);
        }

        return redirect()->route('tenant.fbr-integration')
            ->with('error', $result['message'])
            ->with('fbr_errors', $result['errors'])
            ->with('fbr_http_status', $result['http_status'])
            ->with('fbr_raw_response', $result['response']);
    }

    public function saveProduction(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $prodUrl = $request->input('production_url') ?: $request->input('fbr_production_url') ?: config('services.fbr.production_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata');
        $prodValUrl = $request->input('production_validation_url') ?: $request->input('fbr_production_validation_url') ?: config('services.fbr.production_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata');
        $token = $request->input('production_token') ?: $request->input('fbr_production_token');

        $updateData = [
            'production_url' => trim($prodUrl),
            'production_validation_url' => trim($prodValUrl),
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
        $prodUrl = $request->input('production_url') ?: $request->input('fbr_production_url') ?: $business->production_url ?: config('services.fbr.production_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata');
        $prodValUrl = $request->input('production_validation_url') ?: $request->input('fbr_production_validation_url') ?: $business->production_validation_url ?: config('services.fbr.production_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata');
        $token = $request->input('production_token') ?: $request->input('fbr_production_token') ?: $business->production_token ?: $business->fbr_production_token;

        if (empty($token)) {
            return redirect()->route('tenant.fbr-integration')->with('error', 'Please provide a Production Token before testing connection.');
        }

        $token = trim($token);

        // Perform live FBR Reference API verification
        $result = $this->fbrService->verifyConnection($business, 'production', $token);

        $business->update([
            'production_url' => trim($prodUrl),
            'production_validation_url' => trim($prodValUrl),
            'production_token' => $token,
            'fbr_production_token' => $token,
            'production_verified_at' => now(),
            'production_connection_status' => $result['success'] ? 'connected' : 'failed',
        ]);

        FbrSubmission::create([
            'business_id' => $business->id,
            'environment' => 'production',
            'endpoint' => $result['endpoint'],
            'request_payload' => ['action' => 'verify_connection', 'service' => 'FBR Provinces Reference API'],
            'response_payload' => $result['response'],
            'http_status' => $result['http_status'] ?: 500,
            'outcome' => $result['success'] ? 'success' : ($result['http_status'] === 0 ? 'network_error' : 'rejected'),
            'error_message' => $result['success'] ? null : implode('; ', $result['errors'] ?: [$result['message']]),
            'ip_address' => $request->ip(),
            'executed_at' => now(),
        ]);

        ActivityLog::record(
            $result['success'] ? 'fbr_connection_verified' : 'fbr_connection_failed',
            $result['message'],
            ['env' => 'production', 'http_status' => $result['http_status']],
            $business->id
        );

        if ($result['success']) {
            return redirect()->route('tenant.fbr-integration')->with('success', $result['message']);
        }

        return redirect()->route('tenant.fbr-integration')
            ->with('error', $result['message'])
            ->with('fbr_errors', $result['errors'])
            ->with('fbr_http_status', $result['http_status'])
            ->with('fbr_raw_response', $result['response']);
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
