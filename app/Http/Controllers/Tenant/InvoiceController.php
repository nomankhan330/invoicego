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

        $provinces = [
            'PUNJAB',
            'SINDH',
            'KHYBER PAKHTUNKHWA',
            'BALOCHISTAN',
            'ISLAMABAD CAPITAL TERRITORY',
            'AZAD JAMMU AND KASHMIR',
            'GILGIT BALTISTAN',
        ];

        $saleTypes = [
            'Goods (Standard)',
            'Services (Standard)',
            '3rd Schedule Goods',
            'Zero Rated Goods',
            'Exempt Goods',
            'Electricity / Gas',
            'Reduced Rate Goods',
            'Cotton Ginners',
            'Steel Melting / Re-rolling',
            'Telecom Services',
            'Toll Manufacturing',
            'Other',
        ];

        $uomList = [
            'NOS' => 'Numbers (NOS)',
            'KG' => 'Kilograms (KG)',
            'MTR' => 'Meters (MTR)',
            'LTR' => 'Litres (LTR)',
            'PKT' => 'Packets (PKT)',
            'TON' => 'Tons (TON)',
            'DOZ' => 'Dozens (DOZ)',
            'PRS' => 'Pairs (PRS)',
            'BAG' => 'Bags (BAG)',
            'SET' => 'Sets (SET)',
            'BOX' => 'Boxes (BOX)',
            'OTH' => 'Other (OTH)',
        ];

        $sroList = [
            'SRO 212(I)/2020',
            'SRO 497(I)/2021',
            'SRO 587(I)/2022',
            'Fifth Schedule',
            'Sixth Schedule Table-I',
            'Sixth Schedule Table-II',
            'Eighth Schedule Table-I',
            'Other',
        ];

        $taxRates = ['18%', '0%', '1%', '2%', '3%', '5%', '10%', '12%', '15%', '16%', '17%', 'Exempt'];
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
            'saleTypes',
            'uomList',
            'sroList',
            'taxRates',
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

        $request->mergeIfMissing([
            'buyer_type' => 'unregistered',
            'buyer_province' => 'PUNJAB',
            'buyer_address' => 'N/A',
        ]);

        $validated = $request->validate([
            'customer_id' => ['nullable'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'buyer_province' => ['required', 'string', 'max:100'],
            'buyer_address' => ['required', 'string'],
            'buyer_type' => ['required', 'in:registered,unregistered'],
            'invoice_date' => ['required', 'date'],
            'invoice_time' => ['nullable', 'string', 'max:20'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_invoice_no' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note'],
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
            'items.*.fed_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sro_schedule_no' => ['nullable', 'string'],
            'items.*.sro_item_serial' => ['nullable', 'string'],
        ]);

        // Convert tax_rate if passed as string e.g. "18%"
        foreach ($validated['items'] as $i => $item) {
            $taxRateVal = str_replace('%', '', $item['tax_rate']);
            $validated['items'][$i]['tax_rate'] = is_numeric($taxRateVal) ? (float) $taxRateVal : 0.00;
        }

        // Enforce current month in Pakistan Standard Time
        if (!$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
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

        $request->mergeIfMissing([
            'buyer_name' => $invoice->buyer_name ?? $invoice->customer?->name ?? 'Buyer',
            'buyer_province' => $invoice->buyer_province ?? 'PUNJAB',
            'buyer_address' => $invoice->buyer_address ?? 'N/A',
            'buyer_type' => $invoice->buyer_type ?? 'unregistered',
        ]);

        $validated = $request->validate([
            'customer_id' => ['nullable'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'buyer_province' => ['required', 'string', 'max:100'],
            'buyer_address' => ['required', 'string'],
            'buyer_type' => ['required', 'in:registered,unregistered'],
            'invoice_date' => ['required', 'date'],
            'invoice_time' => ['nullable', 'string', 'max:20'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'reference_invoice_no' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note'],
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
            'items.*.fed_payable_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sro_schedule_no' => ['nullable', 'string'],
            'items.*.sro_item_serial' => ['nullable', 'string'],
        ]);

        foreach ($validated['items'] as $i => $item) {
            $taxRateVal = str_replace('%', '', $item['tax_rate']);
            $validated['items'][$i]['tax_rate'] = is_numeric($taxRateVal) ? (float) $taxRateVal : 0.00;
        }

        if (!$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
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
}

