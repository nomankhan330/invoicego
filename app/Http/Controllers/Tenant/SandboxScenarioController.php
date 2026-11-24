<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FbrSubmission;
use App\Models\SandboxScenario;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class SandboxScenarioController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected FbrIntegrationServiceInterface $fbrService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();
        $scenarios = SandboxScenario::orderBy('scenario_code')->get();

        // Get map of passed/failed statuses for this business
        $progress = $business->sandboxScenarios()->get()->keyBy('id');

        // Group recent attempt logs by scenario code
        $recentAttempts = FbrSubmission::where('business_id', $business->id)
            ->whereNotNull('scenario_code')
            ->latest('id')
            ->take(100)
            ->get()
            ->groupBy('scenario_code');

        return view('tenant.scenarios.index', compact('business', 'scenarios', 'progress', 'recentAttempts'));
    }

    public function validateScenario(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $scenarioCode = $request->input('scenario_code', 'SN001');

        $rawPayload = $request->input('payload') ?? $request->input('custom_payload');
        $decoded = [];

        if (is_string($rawPayload)) {
            $decoded = json_decode($rawPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'http_status' => 422,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg(),
                    'errors' => ['JSON Syntax Error: ' . json_last_error_msg()],
                    'fbr_response' => [],
                ], 422);
            }
        } elseif (is_array($rawPayload)) {
            $decoded = $rawPayload;
        }

        $result = $this->fbrService->validateSandboxPayload($business, $scenarioCode, $decoded);

        return response()->json($result, 200);
    }

    public function run(Request $request, string $scenarioCode)
    {
        $business = $this->tenantService->getActiveBusiness();
        $scenario = SandboxScenario::where('scenario_code', $scenarioCode)->firstOrFail();

        $customPayload = null;
        $rawPayload = $request->input('payload') ?? $request->input('custom_payload');
        if (is_string($rawPayload) && trim($rawPayload) !== '') {
            $decoded = json_decode($rawPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'http_status' => 422,
                    'message' => 'Invalid JSON syntax: ' . json_last_error_msg(),
                    'errors' => ['JSON Syntax Error: ' . json_last_error_msg()],
                ], 422);
            }
            $customPayload = $decoded;
        } elseif (is_array($rawPayload)) {
            $customPayload = $rawPayload;
        }

        $result = $this->fbrService->runSandboxScenario($business, $scenario, $customPayload);

        ActivityLog::record(
            $result['success'] ? 'sandbox_scenario_run' : 'sandbox_scenario_failed',
            $result['message'] ?? "Executed Sandbox Scenario {$scenario->scenario_code} - {$scenario->title}.",
            [
                'scenario_code' => $scenario->scenario_code,
                'http_status' => $result['http_status'] ?? null,
                'invoice_number' => $result['invoice_number'] ?? null,
                'errors' => $result['errors'] ?? [],
            ],
            $business->id
        );

        if ($request->expectsJson() || $request->ajax() || $request->isJson()) {
            return response()->json([
                'success' => (bool) ($result['success'] ?? false),
                'http_status' => $result['http_status'] ?? 200,
                'message' => $result['message'] ?? '',
                'errors' => $result['errors'] ?? [],
                'invoice_number' => $result['invoice_number'] ?? null,
                'endpoint' => $result['endpoint'] ?? null,
                'fbr_response' => $result['response'] ?? [],
                'result' => $result,
            ], 200);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
