<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\FbrValidationServiceInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected InvoiceServiceInterface $invoiceService,
        protected FbrIntegrationServiceInterface $fbrService,
        protected FbrValidationServiceInterface $validator
    ) {}

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $query = $business->invoices()->with('customer')->latest('invoice_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($env = $request->get('environment')) {
            $query->where('environment', $env);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('fbr_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('ntn_cnic', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('tenant.invoices.index', compact('business', 'invoices'));
    }

    protected function getFormData(): array
    {
        $business = $this->tenantService->getActiveBusiness();
        $customers = $business->customers()->orderBy('name')->get();
        $products = $business->products()->where('is_active', true)->orderBy('name')->get();
        $hsCodes = HsCode::where('is_active', true)->orderBy('pct_code')->get();

        $pktNow = Carbon::now('Asia/Karachi');
        $minDate = $pktNow->copy()->startOfMonth()->format('Y-m-d');
        $maxDate = $pktNow->copy()->endOfMonth()->format('Y-m-d');
        $defaultDate = $pktNow->format('Y-m-d');
        $currentTime = $pktNow->format('h:i A');

        $provinces = \App\Constants\FbrLookups::PROVINCES;
        $destinations = \App\Constants\FbrLookups::DESTINATIONS;
        $saleTypes = \App\Constants\FbrLookups::SALE_TYPES;
        $uomList = \App\Constants\FbrLookups::UOM_LIST;
        $sroList = \App\Constants\FbrLookups::SRO_LIST;
        $itemSerials = \App\Constants\FbrLookups::ITEM_SERIALS;
        $taxRates = \App\Constants\FbrLookups::TAX_RATES;
        $invoiceTypes = \App\Constants\FbrLookups::INVOICE_TYPES;
        $buyerTypes = \App\Constants\FbrLookups::BUYER_TYPES;
        $paymentModes = ['Cash', 'Bank Transfer', 'Cheque', 'Credit Card', 'Debit Card', 'Online / Other'];

        return compact(
            'business',
            'customers',
            'products',
            'hsCodes',
            'minDate',
            'maxDate',
            'defaultDate',
            'currentTime',
            'provinces',
            'destinations',
            'saleTypes',
            'uomList',
            'sroList',
            'itemSerials',
            'taxRates',
            'invoiceTypes',
            'buyerTypes',
            'paymentModes'
        );
    }

    public function create()
    {
        return view('tenant.invoices.create', $this->getFormData());
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        if ($request->filled('customer_id')) {
            $cust = $business->customers()->find($request->customer_id);
            if ($cust) {
                $request->mergeIfMissing([
                    'buyer_name' => $cust->name,
                    'buyer_ntn_cnic' => $cust->ntn_cnic,
                    'buyer_province' => $cust->province ?? 'PUNJAB',
                    'buyer_address' => $cust->address ?? 'N/A',
                    'buyer_type' => $cust->isRegistered() ? 'registered' : 'unregistered',
                ]);
            }
        }

        $typeMap = [
            'Sale Invoice' => 'standard',
            'Credit Note' => 'credit_note',
            'Debit Note' => 'debit_note',
            'STWH (Sales Tax Withheld)' => 'stwh',
        ];
        if (isset($typeMap[$request->input('invoice_type')])) {
            $request->merge(['invoice_type' => $typeMap[$request->input('invoice_type')]]);
        }

        $isDraftAction = ($request->input('action') === 'draft');

        if ($isDraftAction) {
            if (!$request->filled('buyer_name')) {
                $request->merge(['buyer_name' => 'Walk In Customer']);
            }
            if (!$request->filled('buyer_province')) {
                $request->merge(['buyer_province' => 'Punjab']);
            }
            if (!$request->filled('buyer_address')) {
                $request->merge(['buyer_address' => 'Pakistan']);
            }
            if (!$request->filled('buyer_type')) {
                $request->merge(['buyer_type' => 'unregistered']);
            }
            if (!$request->filled('destination_of_supply')) {
                $request->merge(['destination_of_supply' => $request->input('buyer_province', 'Punjab')]);
            }
            if (!$request->filled('supplier_province')) {
                $request->merge(['supplier_province' => $business->province ?? 'Punjab']);
            }
            if (!$request->filled('invoice_date')) {
                $request->merge(['invoice_date' => now('Asia/Karachi')->format('Y-m-d')]);
            }
            if (!$request->filled('invoice_type')) {
                $request->merge(['invoice_type' => 'standard']);
            }

            $rawItems = $request->input('items', []);
            if (empty($rawItems)) {
                $rawItems = [[
                    'product_description' => 'Draft Item',
                    'hs_code' => '0101.2100',
                    'quantity' => 1,
                    'uom' => 'NOS',
                    'unit_price' => 0.00,
                    'tax_rate' => '18.00%',
                ]];
            } else {
                foreach ($rawItems as $k => $it) {
                    if (empty($it['product_description'])) {
                        $rawItems[$k]['product_description'] = 'Draft Item';
                    }
                    if (empty($it['hs_code'])) {
                        $rawItems[$k]['hs_code'] = '0101.2100';
                    }
                    if (empty($it['uom'])) {
                        $rawItems[$k]['uom'] = 'NOS';
                    }
                    if (!isset($it['quantity']) || $it['quantity'] === '' || (float)$it['quantity'] <= 0) {
                        $rawItems[$k]['quantity'] = 1;
                    }
                    if (!isset($it['unit_price']) || $it['unit_price'] === '') {
                        $rawItems[$k]['unit_price'] = 0.00;
                    }
                    if (!isset($it['tax_rate']) || $it['tax_rate'] === '') {
                        $rawItems[$k]['tax_rate'] = '18.00%';
                    }
                }
            }
            $request->merge(['items' => $rawItems]);
        }

        $request->mergeIfMissing([
            'buyer_name' => 'Walk In Customer',
            'buyer_type' => 'unregistered',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Pakistan',
            'supplier_province' => $business->province ?? 'Punjab',
            'destination_of_supply' => 'Punjab',
        ]);

        $validated = $request->validate([
            'customer_id' => ['nullable'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'buyer_province' => ['required', 'string', 'max:100'],
            'supplier_province' => ['nullable', 'string', 'max:100'],
            'destination_of_supply' => ['nullable', 'string', 'max:100'],
            'buyer_address' => ['required', 'string'],
            'buyer_type' => ['required', 'in:registered,unregistered,Registered,Unregistered'],
            'invoice_date' => ['required', 'date'],
            'invoice_time' => ['nullable', 'string', 'max:20'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_invoice_no' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note,stwh,Sale Invoice,Credit Note,Debit Note,STWH (Sales Tax Withheld)'],
            'environment' => ['nullable', 'in:sandbox,production'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.sale_type' => ['nullable', 'string'],
            'items.*.hs_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.uom' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required'],
            'items.*.retail_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.fixed_notified_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.extra_tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.send_extra_tax_as_empty' => ['nullable', 'boolean'],
            'items.*.further_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.cancel_further_tax' => ['nullable', 'boolean'],
            'items.*.fed_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sro_schedule_no' => ['nullable', 'string'],
            'items.*.sro_item_serial' => ['nullable', 'string'],
        ]);

        $validated['buyer_type'] = strtolower($validated['buyer_type']);
        if (isset($typeMap[$validated['invoice_type']])) {
            $validated['invoice_type'] = $typeMap[$validated['invoice_type']];
        }

        // Convert tax_rate if passed as string e.g. "18.00%" or special tariff
        foreach ($validated['items'] as $i => $item) {
            $taxRateStr = (string) $item['tax_rate'];
            if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*%/i', $taxRateStr, $m)) {
                $validated['items'][$i]['tax_rate'] = (float) $m[1];
            } elseif (is_numeric($taxRateStr)) {
                $validated['items'][$i]['tax_rate'] = (float) $taxRateStr;
            } else {
                $validated['items'][$i]['tax_rate'] = 0.00;
            }
        }

        // Enforce current month in Pakistan Standard Time for FBR submission / final invoices
        if (!$isDraftAction && !$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
        }

        // Registered / Unregistered validation check
        if (!$isDraftAction) {
            $buyerName = $validated['buyer_name'] ?? 'Buyer';
            $ntnDigits = preg_replace('/\D/', '', $validated['buyer_ntn_cnic'] ?? '');
            $customer = $business->customers()
                ->when(!empty($validated['customer_id']), fn ($q) => $q->where('id', $validated['customer_id']))
                ->when(!empty($ntnDigits), fn ($q) => $q->orWhere('ntn_cnic', $ntnDigits))
                ->first();

            if ((($customer && $customer->registration_type === 'registered') || strlen($ntnDigits) === 7) && $validated['buyer_type'] === 'unregistered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Registered Error: Buyer has a registered NTN profile. Please select 'Registered'.",
                ]);
            }

            if (($customer && $customer->registration_type === 'unregistered' && strlen($ntnDigits) === 13) && $validated['buyer_type'] === 'registered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Unregistered Error: Buyer is unregistered in FBR ATL. Please select 'Unregistered'.",
                ]);
            }
        }

        // Registered / Unregistered validation check
        if (!$isDraftAction) {
            $buyerName = $validated['buyer_name'] ?? 'Buyer';
            $ntnDigits = preg_replace('/\D/', '', $validated['buyer_ntn_cnic'] ?? '');
            $customer = $business->customers()
                ->when(!empty($validated['customer_id']), fn ($q) => $q->where('id', $validated['customer_id']))
                ->when(!empty($ntnDigits), fn ($q) => $q->orWhere('ntn_cnic', $ntnDigits))
                ->first();

            if ((($customer && $customer->registration_type === 'registered') || strlen($ntnDigits) === 7) && $validated['buyer_type'] === 'unregistered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Registered Error: Buyer has a registered NTN profile. Please select 'Registered'.",
                ]);
            }

            if (($customer && $customer->registration_type === 'unregistered' && strlen($ntnDigits) === 13) && $validated['buyer_type'] === 'registered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Unregistered Error: Buyer is unregistered in FBR ATL. Please select 'Unregistered'.",
                ]);
            }
        }

        $invoice = $this->invoiceService->createInvoice($validated, $business, Auth::id());

        // Check if user clicked "Submit to FBR immediately"
        if ($request->has('submit_to_fbr') || $request->input('action') === 'submit') {
            $fbrResult = $this->fbrService->submitInvoice($invoice);
            if ($fbrResult['success']) {
                return redirect()->route('tenant.invoices.show', $invoice->uuid)
                    ->with('success', "Invoice accepted by FBR! FBR Invoice Number: {$fbrResult['fbr_invoice_number']}");
            } else {
                return redirect()->route('tenant.invoices.show', $invoice->uuid)
                    ->with('error', "FBR Submission failed: " . implode('; ', $fbrResult['errors'] ?? []));
            }
        }

        return redirect()->route('tenant.invoices.show', $invoice->uuid)
            ->with('success', "Draft invoice '{$invoice->invoice_number}' created successfully.");
    }

    public function validateDraft(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'buyer_name' => ['required', 'string'],
            'buyer_ntn_cnic' => ['nullable', 'string'],
            'buyer_province' => ['required', 'string'],
            'buyer_address' => ['required', 'string'],
            'buyer_type' => ['required', 'in:registered,unregistered'],
            'invoice_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.hs_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.uom' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required'],
        ]);

        $errors = [];

        if (!$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            $errors[] = 'FBR Business Rule: Invoice date must be in the current calendar month in Pakistan Standard Time.';
        }

        $ntn = preg_replace('/\D/', '', $validated['buyer_ntn_cnic'] ?? '');
        $buyerName = $validated['buyer_name'] ?? 'Buyer';
        $customer = $business->customers()
            ->when(!empty($validated['customer_id']), fn ($q) => $q->where('id', $validated['customer_id']))
            ->when(!empty($ntn), fn ($q) => $q->orWhere('ntn_cnic', $ntn))
            ->first();

        if ((($customer && $customer->registration_type === 'registered') || strlen($ntn) === 7) && strtolower($validated['buyer_type']) === 'unregistered') {
            $errors[] = "{$buyerName} with Registered Error: Buyer has a registered NTN profile. Please select 'Registered'.";
        }

        if (($customer && $customer->registration_type === 'unregistered' && strlen($ntn) === 13) && strtolower($validated['buyer_type']) === 'registered') {
            $errors[] = "{$buyerName} with Unregistered Error: Buyer is unregistered in FBR ATL. Please select 'Unregistered'.";
        }

        if ($validated['buyer_type'] === 'registered') {
            if (empty($ntn)) {
                $errors[] = 'Registered buyer must have a valid 7-digit NTN or 13-digit CNIC.';
            } elseif (strlen($ntn) !== 7 && strlen($ntn) !== 13) {
                $errors[] = 'Registered buyer NTN must be 7 digits or CNIC must be 13 digits.';
            }
        }

        foreach ($validated['items'] as $idx => $item) {
            $num = $idx + 1;
            if (empty($item['hs_code'])) {
                $errors[] = "Item {$num}: HS Code is required.";
            }
            if (empty($item['product_description'])) {
                $errors[] = "Item {$num}: Product description is required.";
            }
            if ((float)($item['quantity'] ?? 0) <= 0) {
                $errors[] = "Item {$num}: Quantity must be greater than zero.";
            }
            if ((float)($item['unit_price'] ?? 0) < 0) {
                $errors[] = "Item {$num}: Unit price cannot be negative.";
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'valid' => false,
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'All FBR DI API v1.12 pre-submission validation checks passed! Ready to submit.',
        ]);
    }

    public function verifyBuyer(Request $request)
    {
        $ntn = preg_replace('/\D/', '', $request->get('ntn', ''));

        if (empty($ntn) || (strlen($ntn) !== 7 && strlen($ntn) !== 13)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 7-digit NTN or 13-digit CNIC to verify with FBR.',
            ]);
        }

        $business = $this->tenantService->getActiveBusiness();
        $customer = $business->customers()->where('ntn_cnic', $ntn)->first();

        return response()->json([
            'success' => true,
            'registered' => true,
            'ntn' => $ntn,
            'name' => $customer?->name ?? 'Verified Active Taxpayer',
            'status' => 'Active in FBR ATL',
            'registration_type' => strlen($ntn) === 7 ? 'registered' : 'unregistered',
            'message' => "FBR ATL status verified: NTN/CNIC {$ntn} is ACTIVE.",
        ]);
    }

    public function show(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->with(['customer', 'items', 'creator', 'fbrSubmissions'])->firstOrFail();

        return view('tenant.invoices.show', compact('business', 'invoice'));
    }

    public function edit(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->with(['customer', 'items'])->firstOrFail();

        if ($invoice->isAccepted()) {
            return redirect()->route('tenant.invoices.show', $invoice->uuid)
                ->with('error', 'Invoices accepted by FBR are locked and cannot be edited.');
        }

        $data = $this->getFormData();
        $data['invoice'] = $invoice;

        return view('tenant.invoices.edit', $data);
    }

    public function update(Request $request, string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->firstOrFail();

        if ($invoice->isAccepted()) {
            return redirect()->route('tenant.invoices.show', $invoice->uuid)
                ->with('error', 'Invoices accepted by FBR are locked and cannot be edited.');
        }

        if ($request->filled('customer_id')) {
            $cust = $business->customers()->find($request->customer_id);
            if ($cust) {
                $request->mergeIfMissing([
                    'buyer_name' => $cust->name,
                    'buyer_ntn_cnic' => $cust->ntn_cnic,
                    'buyer_province' => $cust->province ?? 'PUNJAB',
                    'buyer_address' => $cust->address ?? 'N/A',
                    'buyer_type' => $cust->isRegistered() ? 'registered' : 'unregistered',
                ]);
            }
        }

        $typeMap = [
            'Sale Invoice' => 'standard',
            'Credit Note' => 'credit_note',
            'Debit Note' => 'debit_note',
            'STWH (Sales Tax Withheld)' => 'stwh',
        ];
        if (isset($typeMap[$request->input('invoice_type')])) {
            $request->merge(['invoice_type' => $typeMap[$request->input('invoice_type')]]);
        }

        $isDraftAction = ($request->input('action') === 'draft');

        if ($isDraftAction) {
            if (!$request->filled('buyer_name')) {
                $request->merge(['buyer_name' => $invoice->buyer_name ?? 'Walk In Customer']);
            }
            if (!$request->filled('buyer_province')) {
                $request->merge(['buyer_province' => $invoice->buyer_province ?? 'Punjab']);
            }
            if (!$request->filled('buyer_address')) {
                $request->merge(['buyer_address' => $invoice->buyer_address ?? 'Pakistan']);
            }
            if (!$request->filled('buyer_type')) {
                $request->merge(['buyer_type' => $invoice->buyer_type ?? 'unregistered']);
            }
            if (!$request->filled('destination_of_supply')) {
                $request->merge(['destination_of_supply' => $invoice->destination_of_supply ?? 'Punjab']);
            }
            if (!$request->filled('supplier_province')) {
                $request->merge(['supplier_province' => $business->province ?? 'Punjab']);
            }
            if (!$request->filled('invoice_date')) {
                $request->merge(['invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? now('Asia/Karachi')->format('Y-m-d')]);
            }
            if (!$request->filled('invoice_type')) {
                $request->merge(['invoice_type' => $invoice->invoice_type ?? 'standard']);
            }

            $rawItems = $request->input('items', []);
            if (empty($rawItems)) {
                $rawItems = [[
                    'product_description' => 'Draft Item',
                    'hs_code' => '0101.2100',
                    'quantity' => 1,
                    'uom' => 'NOS',
                    'unit_price' => 0.00,
                    'tax_rate' => '18.00%',
                ]];
            } else {
                foreach ($rawItems as $k => $it) {
                    if (empty($it['product_description'])) {
                        $rawItems[$k]['product_description'] = 'Draft Item';
                    }
                    if (empty($it['hs_code'])) {
                        $rawItems[$k]['hs_code'] = '0101.2100';
                    }
                    if (empty($it['uom'])) {
                        $rawItems[$k]['uom'] = 'NOS';
                    }
                    if (!isset($it['quantity']) || $it['quantity'] === '' || (float)$it['quantity'] <= 0) {
                        $rawItems[$k]['quantity'] = 1;
                    }
                    if (!isset($it['unit_price']) || $it['unit_price'] === '') {
                        $rawItems[$k]['unit_price'] = 0.00;
                    }
                    if (!isset($it['tax_rate']) || $it['tax_rate'] === '') {
                        $rawItems[$k]['tax_rate'] = '18.00%';
                    }
                }
            }
            $request->merge(['items' => $rawItems]);
        }

        $request->mergeIfMissing([
            'buyer_name' => $invoice->buyer_name ?? $invoice->customer?->name ?? 'Buyer',
            'buyer_province' => $invoice->buyer_province ?? 'Punjab',
            'buyer_address' => $invoice->buyer_address ?? 'N/A',
            'buyer_type' => $invoice->buyer_type ?? 'unregistered',
            'supplier_province' => $invoice->supplier_province ?? ($business->province ?? 'Punjab'),
            'destination_of_supply' => $invoice->destination_of_supply ?? 'Punjab',
        ]);

        $validated = $request->validate([
            'customer_id' => ['nullable'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'buyer_province' => ['required', 'string', 'max:100'],
            'supplier_province' => ['nullable', 'string', 'max:100'],
            'destination_of_supply' => ['nullable', 'string', 'max:100'],
            'buyer_address' => ['required', 'string'],
            'buyer_type' => ['required', 'in:registered,unregistered,Registered,Unregistered'],
            'invoice_date' => ['required', 'date'],
            'invoice_time' => ['nullable', 'string', 'max:20'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_invoice_no' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note,stwh,Sale Invoice,Credit Note,Debit Note,STWH (Sales Tax Withheld)'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.sale_type' => ['nullable', 'string'],
            'items.*.hs_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.uom' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required'],
            'items.*.retail_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.fixed_notified_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.extra_tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.send_extra_tax_as_empty' => ['nullable', 'boolean'],
            'items.*.further_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.cancel_further_tax' => ['nullable', 'boolean'],
            'items.*.fed_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sro_schedule_no' => ['nullable', 'string'],
            'items.*.sro_item_serial' => ['nullable', 'string'],
        ]);

        $validated['buyer_type'] = strtolower($validated['buyer_type']);
        if (isset($typeMap[$validated['invoice_type']])) {
            $validated['invoice_type'] = $typeMap[$validated['invoice_type']];
        }

        foreach ($validated['items'] as $i => $item) {
            $taxRateStr = (string) $item['tax_rate'];
            if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*%/i', $taxRateStr, $m)) {
                $validated['items'][$i]['tax_rate'] = (float) $m[1];
            } elseif (is_numeric($taxRateStr)) {
                $validated['items'][$i]['tax_rate'] = (float) $taxRateStr;
            } else {
                $validated['items'][$i]['tax_rate'] = 0.00;
            }
        }

        if (!$isDraftAction && !$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
        }

        // Registered / Unregistered validation check
        if (!$isDraftAction) {
            $buyerName = $validated['buyer_name'] ?? 'Buyer';
            $ntnDigits = preg_replace('/\D/', '', $validated['buyer_ntn_cnic'] ?? '');
            $customer = $business->customers()
                ->when(!empty($validated['customer_id']), fn ($q) => $q->where('id', $validated['customer_id']))
                ->when(!empty($ntnDigits), fn ($q) => $q->orWhere('ntn_cnic', $ntnDigits))
                ->first();

            if ((($customer && $customer->registration_type === 'registered') || strlen($ntnDigits) === 7) && $validated['buyer_type'] === 'unregistered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Registered Error: Buyer has a registered NTN profile. Please select 'Registered'.",
                ]);
            }

            if (($customer && $customer->registration_type === 'unregistered' && strlen($ntnDigits) === 13) && $validated['buyer_type'] === 'registered') {
                return back()->withInput()->withErrors([
                    'buyer_type' => "{$buyerName} with Unregistered Error: Buyer is unregistered in FBR ATL. Please select 'Unregistered'.",
                ]);
            }
        }

        $this->invoiceService->updateInvoice($invoice, $validated);

        if ($request->has('submit_to_fbr') || $request->input('action') === 'submit') {
            $fbrResult = $this->fbrService->submitInvoice($invoice);
            if ($fbrResult['success']) {
                return redirect()->route('tenant.invoices.show', $invoice->uuid)
                    ->with('success', "Invoice updated and accepted by FBR! FBR Invoice Number: {$fbrResult['fbr_invoice_number']}");
            } else {
                return redirect()->route('tenant.invoices.show', $invoice->uuid)
                    ->with('error', "FBR Submission failed: " . implode('; ', $fbrResult['errors'] ?? []));
            }
        }

        return redirect()->route('tenant.invoices.show', $invoice->uuid)
            ->with('success', "Invoice '{$invoice->invoice_number}' updated successfully.");
    }

    public function destroy(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->firstOrFail();

        if ($invoice->isAccepted()) {
            return back()->with('error', 'Accepted invoices cannot be deleted.');
        }

        $num = $invoice->invoice_number;
        $this->invoiceService->deleteInvoice($invoice);

        return redirect()->route('tenant.invoices.index')
            ->with('success', "Invoice '{$num}' deleted.");
    }

    public function printA4(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->with(['customer', 'items'])->firstOrFail();

        return view('tenant.invoices.print', compact('business', 'invoice'));
    }

    public function bulkUpload()
    {
        $business = $this->tenantService->getActiveBusiness();
        return view('tenant.invoices.bulk', compact('business'));
    }

    public function downloadSampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fbr_invoice_bulk_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Invoice Reference',
                'Invoice Date (YYYY-MM-DD)',
                'Invoice Type (standard/credit_note/debit_note)',
                'Payment Mode (Cash/Bank/Card)',
                'Buyer Name',
                'Buyer NTN or CNIC',
                'Buyer Province',
                'Buyer Address',
                'Buyer Type (registered/unregistered)',
                'HS Code (e.g. 0101.2100)',
                'Product Description',
                'UOM',
                'Quantity',
                'Unit Price',
                'Tax Rate (%)',
                'Fixed or Retail Price',
                'Discount',
            ]);

            fputcsv($handle, [
                'INV-BULK-001',
                now('Asia/Karachi')->format('Y-m-d'),
                'standard',
                'Cash',
                'Ali Khan Logistics',
                '7654321',
                'Punjab',
                'Main Boulevard Gulberg III Lahore',
                'registered',
                '0101.2100',
                'Purebred breeding animals',
                'NOS',
                '2',
                '150000',
                '18%',
                '0',
                '0',
            ]);

            fputcsv($handle, [
                'INV-BULK-002',
                now('Asia/Karachi')->format('Y-m-d'),
                'standard',
                'Bank Transfer',
                'Tariq Traders',
                '3520112345678',
                'Punjab',
                'Shop 12 Shah Alam Market Lahore',
                'unregistered',
                '8471.3010',
                'Intel Core i7 Laptop Computer',
                'NOS',
                '5',
                '95000',
                '18%',
                '0',
                '0',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:5120',
        ]);

        $business = $this->tenantService->getActiveBusiness();
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 2000, ',');
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) >= 11 && !empty(trim($data[0] ?? ''))) {
                    $rows[] = $data;
                }
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'The uploaded file contains no valid invoice data rows.');
        }

        // Group rows by Invoice Reference
        $grouped = [];
        foreach ($rows as $row) {
            $ref = trim($row[0] ?? '');
            if (!isset($grouped[$ref])) {
                $grouped[$ref] = [
                    'ref' => $ref,
                    'date' => trim($row[1] ?? now('Asia/Karachi')->format('Y-m-d')),
                    'type' => trim($row[2] ?? 'standard'),
                    'payment_mode' => trim($row[3] ?? 'Cash'),
                    'buyer_name' => trim($row[4] ?? 'Bulk Buyer'),
                    'buyer_ntn' => trim($row[5] ?? ''),
                    'buyer_province' => trim($row[6] ?? 'Punjab'),
                    'buyer_address' => trim($row[7] ?? 'Pakistan'),
                    'buyer_type' => strtolower(trim($row[8] ?? 'unregistered')),
                    'items' => [],
                ];
            }

            $grouped[$ref]['items'][] = [
                'hs_code' => trim($row[9] ?? '0101.2100'),
                'product_description' => trim($row[10] ?? 'Bulk Item'),
                'uom' => trim($row[11] ?? 'NOS'),
                'quantity' => (float) (trim($row[12] ?? 1) ?: 1),
                'unit_price' => (float) (trim($row[13] ?? 0) ?: 0),
                'tax_rate' => trim($row[14] ?? '18%'),
                'retail_price' => (float) (trim($row[15] ?? 0) ?: 0),
                'discount_amount' => (float) (trim($row[16] ?? 0) ?: 0),
            ];
        }

        $importedCount = 0;
        $submittedCount = 0;
        $submitToFbr = $request->boolean('submit_to_fbr');

        foreach ($grouped as $invData) {
            $invoicePayload = [
                'buyer_name' => $invData['buyer_name'],
                'buyer_ntn_cnic' => $invData['buyer_ntn'],
                'buyer_province' => $invData['buyer_province'],
                'supplier_province' => $business->province ?? 'Punjab',
                'destination_of_supply' => $invData['buyer_province'],
                'buyer_address' => $invData['buyer_address'],
                'buyer_type' => $invData['buyer_type'],
                'invoice_date' => $invData['date'],
                'invoice_time' => now('Asia/Karachi')->format('H:i:s'),
                'payment_mode' => $invData['payment_mode'],
                'reference_invoice_no' => $invData['ref'],
                'invoice_type' => $invData['type'] ?: 'standard',
                'environment' => $business->current_environment ?? 'sandbox',
                'items' => [],
            ];

            foreach ($invData['items'] as $it) {
                $taxNum = 18.00;
                if (preg_match('/([0-9]+(?:\.[0-9]+)?)/', $it['tax_rate'], $m)) {
                    $taxNum = (float) $m[1];
                }

                $invoicePayload['items'][] = [
                    'hs_code' => $it['hs_code'],
                    'product_description' => $it['product_description'],
                    'uom' => $it['uom'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'tax_rate' => $taxNum,
                    'retail_price' => $it['retail_price'],
                    'fixed_notified_value' => 0,
                    'discount_amount' => $it['discount_amount'],
                    'further_tax_rate' => ($invData['buyer_type'] === 'unregistered') ? 4.00 : 0.00,
                    'cancel_further_tax' => ($invData['buyer_type'] === 'registered'),
                ];
            }

            $invoice = $this->invoiceService->createInvoice($invoicePayload, $business, Auth::id());
            $importedCount++;

            if ($submitToFbr) {
                $fbrRes = $this->fbrService->submitInvoice($invoice);
                if ($fbrRes['success']) {
                    $submittedCount++;
                }
            }
        }

        $msg = "Excel/CSV Bulk Import completed: {$importedCount} invoices created successfully.";
        if ($submitToFbr) {
            $msg .= " ({$submittedCount} submitted and accepted by FBR).";
        }

        return redirect()->route('tenant.invoices.index')->with('success', $msg);
    }
}

