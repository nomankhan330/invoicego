<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\HsCode;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class HsCodeController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $query = HsCode::where('is_active', true);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pct_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $hsCodes = $query->paginate(20)->withQueryString();

        return view('tenant.hscodes.index', compact('business', 'hsCodes'));
    }
}

