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

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $query = $business->products()->with('hsCode')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('hs_code', 'like', "%{$search}%")
                  ->orWhereHas('hsCode', function ($hq) use ($search) {
                      $hq->where('pct_code', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $hsCodes = HsCode::where('is_active', true)->orderBy('pct_code')->get();

        $uomList = \App\Constants\FbrLookups::UOM_LIST;
        $saleTypes = array_combine(\App\Constants\FbrLookups::SALE_TYPES, \App\Constants\FbrLookups::SALE_TYPES);
        $sroList = array_merge(['' => 'None / Not Applicable'], array_combine(\App\Constants\FbrLookups::SRO_LIST, \App\Constants\FbrLookups::SRO_LIST));

        return view('tenant.products.index', compact('business', 'products', 'hsCodes', 'uomList', 'saleTypes', 'sroList'));
    }

    public function create()
    {
        return redirect()->route('tenant.products.index');
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'hs_code' => ['required', 'string', 'max:20'],
            'uom' => ['required', 'string', 'max:30'],
            'sale_type' => ['required', 'string', 'max:100'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sro_schedule_no' => ['nullable', 'string', 'max:100'],
            'sro_item_serial' => ['nullable', 'string', 'max:100'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $hsCodeRecord = HsCode::where('pct_code', $validated['hs_code'])->first();

        $product = $business->products()->create([
            'sku' => $validated['sku'],
            'name' => $validated['description'], // keep description as canonical item name
            'description' => $validated['description'],
            'hs_code_id' => $hsCodeRecord?->id,
            'hs_code' => $validated['hs_code'],
            'uom' => $validated['uom'],
            'sale_type' => $validated['sale_type'],
            'unit_price' => $validated['unit_price'],
            'default_tax_rate' => $validated['default_tax_rate'] ?? 18.00,
            'sro_schedule_no' => $validated['sro_schedule_no'] ?? null,
            'sro_item_serial' => $validated['sro_item_serial'] ?? null,
            'retail_price' => $validated['retail_price'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('tenant.products.index')
            ->with('success', "Product '{$product->sku}' added successfully.");
    }

    public function update(Request $request, $id)
    {
        $business = $this->tenantService->getActiveBusiness();
        $product = $business->products()->findOrFail($id);

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'hs_code' => ['required', 'string', 'max:20'],
            'uom' => ['required', 'string', 'max:30'],
            'sale_type' => ['required', 'string', 'max:100'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sro_schedule_no' => ['nullable', 'string', 'max:100'],
            'sro_item_serial' => ['nullable', 'string', 'max:100'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $hsCodeRecord = HsCode::where('pct_code', $validated['hs_code'])->first();

        $product->update([
            'sku' => $validated['sku'],
            'name' => $validated['description'],
            'description' => $validated['description'],
            'hs_code_id' => $hsCodeRecord?->id,
            'hs_code' => $validated['hs_code'],
            'uom' => $validated['uom'],
            'sale_type' => $validated['sale_type'],
            'unit_price' => $validated['unit_price'],
            'default_tax_rate' => $validated['default_tax_rate'] ?? 18.00,
            'sro_schedule_no' => $validated['sro_schedule_no'] ?? null,
            'sro_item_serial' => $validated['sro_item_serial'] ?? null,
            'retail_price' => $validated['retail_price'] ?? null,
        ]);

        return redirect()->route('tenant.products.index')
            ->with('success', "Product '{$product->sku}' updated successfully.");
    }

    public function destroy($id)
    {
        $business = $this->tenantService->getActiveBusiness();
        $product = $business->products()->findOrFail($id);
        $sku = $product->sku;
        $product->delete();

        return redirect()->route('tenant.products.index')
            ->with('success', "Product '{$sku}' deleted.");
    }
}
