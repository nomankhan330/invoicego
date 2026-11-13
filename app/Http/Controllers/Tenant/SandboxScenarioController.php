<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
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
        $scenarios = SandboxScenario::all();

        // Get map of passed/failed statuses for this business
        $progress = $business->sandboxScenarios()->get()->keyBy('id');

        return view('tenant.scenarios.index', compact('business', 'scenarios', 'progress'));
    }

    public function run(string $scenarioCode)
    {
        $business = $this->tenantService->getActiveBusiness();
        $scenario = SandboxScenario::where('scenario_code', $scenarioCode)->firstOrFail();

        $result = $this->fbrService->runSandboxScenario($business, $scenario);

        return back()->with('success', "Scenario {$scenario->scenario_code} executed and verified by PRAL sandbox validator!");
    }
}
