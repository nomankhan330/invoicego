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

    public function create()
    {
        $business = $this->tenantService->getActiveBusiness();
        $customers = $business->customers()->orderBy('name')->get();
        $products = $business->products()->where('is_active', true)->orderBy('name')->get();
        $hsCodes = HsCode::where('is_active', true)->orderBy('pct_code')->get();

        $pktNow = Carbon::now('Asia/Karachi');
        $minDate = $pktNow->copy()->startOfMonth()->format('Y-m-d');
        $maxDate = $pktNow->copy()->endOfMonth()->format('Y-m-d');
        $defaultDate = $pktNow->format('Y-m-d');

        return view('tenant.invoices.create', compact('business', 'customers', 'products', 'hsCodes', 'minDate', 'maxDate', 'defaultDate'));
    }

    public function store(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note'],
            'environment' => ['required', 'in:sandbox,production'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.hs_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.uom' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.further_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Enforce current month in Pakistan Standard Time
        if (!$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
        }

        $invoice = $this->invoiceService->createInvoice($validated, $business, Auth::id());

        // Check if user clicked "Submit to FBR immediately"
        if ($request->has('submit_to_fbr')) {
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

    public function show(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->with(['customer', 'items', 'creator', 'fbrSubmissions'])->firstOrFail();

        return view('tenant.invoices.show', compact('business', 'invoice'));
    }

    public function submitToFbr(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->firstOrFail();

        if ($invoice->isAccepted()) {
            return back()->with('info', 'This invoice has already been accepted by FBR.');
        }

        $result = $this->fbrService->submitInvoice($invoice);

        if ($result['success']) {
            return back()->with('success', "Accepted by FBR! FBR Invoice Number: {$result['fbr_invoice_number']}");
        } elseif (($result['status'] ?? '') === 'needs_reconciliation') {
            return back()->with('error', "Network timeout. Invoice marked as 'Needs Reconciliation'.");
        } else {
            return back()->with('error', "FBR Submission Rejected: " . implode('; ', $result['errors'] ?? []));
        }
    }

    public function reconcile(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->firstOrFail();

        $result = $this->fbrService->reconcileInvoice($invoice);

        if ($result['success']) {
            return back()->with('success', "Invoice reconciled! FBR Number: {$result['fbr_invoice_number']}");
        }

        return back()->with('error', $result['message'] ?? 'Reconciliation could not be completed.');
    }

    public function edit(string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->with(['customer', 'items'])->firstOrFail();

        if ($invoice->isAccepted()) {
            return redirect()->route('tenant.invoices.show', $invoice->uuid)
                ->with('error', 'Invoices accepted by FBR are locked and cannot be edited.');
        }

        $customers = $business->customers()->orderBy('name')->get();
        $products = $business->products()->where('is_active', true)->orderBy('sku')->get();
        $hsCodes = HsCode::where('is_active', true)->orderBy('pct_code')->get();

        $pktNow = Carbon::now('Asia/Karachi');
        $minDate = $pktNow->copy()->startOfMonth()->format('Y-m-d');
        $maxDate = $pktNow->copy()->endOfMonth()->format('Y-m-d');

        return view('tenant.invoices.edit', compact('business', 'invoice', 'customers', 'products', 'hsCodes', 'minDate', 'maxDate'));
    }

    public function update(Request $request, string $uuid)
    {
        $business = $this->tenantService->getActiveBusiness();
        $invoice = $business->invoices()->where('uuid', $uuid)->firstOrFail();

        if ($invoice->isAccepted()) {
            return redirect()->route('tenant.invoices.show', $invoice->uuid)
                ->with('error', 'Invoices accepted by FBR are locked and cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'invoice_type' => ['required', 'in:standard,credit_note,debit_note'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.hs_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.uom' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.further_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.st_withheld_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (!$this->validator->isCurrentMonthInPakistan($validated['invoice_date'])) {
            return back()->withInput()->withErrors([
                'invoice_date' => 'FBR Business Rule: Invoices can only be dated for the current calendar month in Pakistan Standard Time.',
            ]);
        }

        $this->invoiceService->updateInvoice($invoice, $validated);

        if ($request->has('submit_to_fbr')) {
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

