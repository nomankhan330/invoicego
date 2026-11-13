<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\HsCode;
use App\Models\Product;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();
        $products = $business->products()->with('hsCode')->latest()->paginate(15);

        return view('tenant.products.index', compact('business', 'products'));
    }

    public function create()
    {
        $business = $this->tenantService->getActiveBusiness();
        $hsCodes = HsCode::where('is_active', true)->orderBy('pct_code')->get();

        return view('tenant.products.create', compact('business', 'hsCodes'));
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'hs_code_id' => ['nullable', 'exists:hs_codes,id'],
            'description' => ['nullable', 'string'],
            'uom' => ['required', 'string', 'max:30'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $product = $business->products()->create($validated);

        return redirect()->route('tenant.products.index')
            ->with('success', "Product '{$product->name}' added successfully.");
    }
}
