<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Contracts\FbrValidationServiceInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        protected FbrValidationServiceInterface $validator
    ) {}

    public function createInvoice(array $data, Business $business, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $business, $userId) {
            if (!empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            } else {
                $customer = Customer::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $data['buyer_name'] ?? 'Walk-in Customer',
                    ],
                    [
                        'ntn_cnic' => $data['buyer_ntn_cnic'] ?? '',
                        'province' => $data['buyer_province'] ?? 'Punjab',
                        'address' => $data['buyer_address'] ?? 'N/A',
                        'registration_type' => ($data['buyer_type'] ?? 'unregistered') === 'registered' ? 'registered' : 'unregistered',
                    ]
                );
            }

            $buyerType = $data['buyer_type'] ?? ($customer ? ($customer->isRegistered() ? 'registered' : 'unregistered') : 'unregistered');
            $isUnregistered = ($buyerType === 'unregistered');

            $environment = $data['environment'] ?? $business->current_environment ?? 'sandbox';

            // Auto-generate invoice number if not provided
            $invoiceNumber = $data['invoice_number'] ?? ('INV-' . date('Ym') . '-' . sprintf('%04d', $business->invoices()->count() + 1));

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'buyer_name' => $data['buyer_name'] ?? $customer->name,
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'] ?? $customer->ntn_cnic,
                'buyer_province' => $data['buyer_province'] ?? $customer->province,
                'buyer_address' => $data['buyer_address'] ?? $customer->address,
                'buyer_type' => $buyerType,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => $data['invoice_type'] ?? 'standard',
                'environment' => $environment,
                'invoice_date' => $data['invoice_date'] ?? Carbon::now('Asia/Karachi')->format('Y-m-d'),
                'invoice_time' => $data['invoice_time'] ?? Carbon::now('Asia/Karachi')->format('h:i A'),
                'payment_mode' => $data['payment_mode'] ?? 'Cash',
                'reference_invoice_no' => $data['reference_invoice_no'] ?? null,
                'supplier_province' => $data['supplier_province'] ?? ($business->province ?? 'Punjab'),
                'destination_of_supply' => $data['destination_of_supply'] ?? ($data['buyer_province'] ?? ($customer->province ?? 'Punjab')),
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            foreach ($data['items'] as $itemData) {
                $calc = $this->validator->calculateItemTotals($itemData, $isUnregistered);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'business_id' => $business->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'hs_code' => $itemData['hs_code'] ?? '8471.30.00',
                    'product_description' => $itemData['product_description'] ?? 'Item',
                    'quantity' => $calc['quantity'],
                    'uom' => $itemData['uom'] ?? 'NOS',
                    'sale_type' => $itemData['sale_type'] ?? 'Goods at Standard Rate (Default)',
                    'unit_price' => $calc['unit_price'],
                    'retail_price' => $calc['retail_price'] ?? $itemData['retail_price'] ?? null,
                    'fixed_notified_value' => $calc['fixed_notified_value'] ?? $itemData['fixed_notified_value'] ?? null,
                    'value_excl_tax' => $calc['value_excl_tax'],
                    'tax_rate' => $calc['tax_rate'],
                    'sales_tax_amount' => $calc['sales_tax_amount'],
                    'further_tax_rate' => $calc['further_tax_rate'],
                    'further_tax_amount' => $calc['further_tax_amount'],
                    'cancel_further_tax' => !empty($itemData['cancel_further_tax']),
                    'extra_tax_amount' => $calc['extra_tax_amount'],
                    'send_extra_tax_as_empty' => !empty($itemData['send_extra_tax_as_empty']),
                    'fed_payable_amount' => $calc['fed_payable_amount'],
                    'st_withheld_rate' => $calc['st_withheld_rate'],
                    'st_withheld_amount' => $calc['st_withheld_amount'],
                    'discount_amount' => $calc['discount_amount'],
                    'sro_schedule_no' => $itemData['sro_schedule_no'] ?? null,
                    'sro_item_serial' => $itemData['sro_item_serial'] ?? null,
                    'total_value_incl_tax' => $calc['total_value_incl_tax'],
                ]);
            }

            return $this->validator->recalculateInvoiceTotals($invoice);
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->isLocked()) {
            throw new \RuntimeException('Cannot modify an invoice that is locked or accepted by FBR.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            $customer = !empty($data['customer_id']) ? Customer::find($data['customer_id']) : $invoice->customer;
            $buyerType = $data['buyer_type'] ?? ($customer ? ($customer->isRegistered() ? 'registered' : 'unregistered') : 'unregistered');
            $isUnregistered = ($buyerType === 'unregistered');

            $invoice->update([
                'customer_id' => $customer ? $customer->id : $invoice->customer_id,
                'buyer_name' => $data['buyer_name'] ?? $invoice->buyer_name,
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'] ?? $invoice->buyer_ntn_cnic,
                'buyer_province' => $data['buyer_province'] ?? $invoice->buyer_province,
                'buyer_address' => $data['buyer_address'] ?? $invoice->buyer_address,
                'buyer_type' => $buyerType,
                'invoice_type' => $data['invoice_type'] ?? $invoice->invoice_type,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'invoice_time' => $data['invoice_time'] ?? $invoice->invoice_time,
                'payment_mode' => $data['payment_mode'] ?? $invoice->payment_mode,
                'reference_invoice_no' => $data['reference_invoice_no'] ?? $invoice->reference_invoice_no,
                'supplier_province' => $data['supplier_province'] ?? $invoice->supplier_province,
                'destination_of_supply' => $data['destination_of_supply'] ?? $invoice->destination_of_supply,
            ]);

            if (isset($data['items'])) {
                $invoice->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $calc = $this->validator->calculateItemTotals($itemData, $isUnregistered);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'business_id' => $invoice->business_id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'hs_code' => $itemData['hs_code'] ?? '8471.30.00',
                        'product_description' => $itemData['product_description'] ?? 'Item',
                        'quantity' => $calc['quantity'],
                        'uom' => $itemData['uom'] ?? 'NOS',
                        'sale_type' => $itemData['sale_type'] ?? 'Goods at Standard Rate (Default)',
                        'unit_price' => $calc['unit_price'],
                        'retail_price' => $calc['retail_price'] ?? $itemData['retail_price'] ?? null,
                        'fixed_notified_value' => $calc['fixed_notified_value'] ?? $itemData['fixed_notified_value'] ?? null,
                        'value_excl_tax' => $calc['value_excl_tax'],
                        'tax_rate' => $calc['tax_rate'],
                        'sales_tax_amount' => $calc['sales_tax_amount'],
                        'further_tax_rate' => $calc['further_tax_rate'],
                        'further_tax_amount' => $calc['further_tax_amount'],
                        'cancel_further_tax' => !empty($itemData['cancel_further_tax']),
                        'extra_tax_amount' => $calc['extra_tax_amount'],
                        'send_extra_tax_as_empty' => !empty($itemData['send_extra_tax_as_empty']),
                        'fed_payable_amount' => $calc['fed_payable_amount'],
                        'st_withheld_rate' => $calc['st_withheld_rate'],
                        'st_withheld_amount' => $calc['st_withheld_amount'],
                        'discount_amount' => $calc['discount_amount'],
                        'sro_schedule_no' => $itemData['sro_schedule_no'] ?? null,
                        'sro_item_serial' => $itemData['sro_item_serial'] ?? null,
                        'total_value_incl_tax' => $calc['total_value_incl_tax'],
                    ]);
                }
            }

            return $this->validator->recalculateInvoiceTotals($invoice);
        });
    }

    public function deleteInvoice(Invoice $invoice): bool
    {
        if ($invoice->isLocked()) {
            throw new \RuntimeException('Cannot delete an invoice that is locked or submitted to FBR.');
        }

        return $invoice->delete();
    }

    public function getDashboardMetrics(Business $business): array
    {
        $pktNow = Carbon::now('Asia/Karachi');
        $startOfMonth = $pktNow->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $pktNow->copy()->endOfMonth()->format('Y-m-d');

        // Production invoices query
        $prodInvoices = $business->invoices()->where('environment', 'production');
        $sandboxInvoicesCount = $business->invoices()->where('environment', 'sandbox')->count();

        $totalProdCount = (clone $prodInvoices)->count();
        $thisMonthCount = (clone $prodInvoices)->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->count();
        $acceptedCount = (clone $prodInvoices)->where('status', 'accepted')->count();
        $failedCount = (clone $prodInvoices)->where('status', 'failed')->count();
        $draftsCount = (clone $prodInvoices)->where('status', 'draft')->count();
        $pendingCount = (clone $prodInvoices)->where('status', 'pending')->count();
        $reconcileCount = (clone $prodInvoices)->where('status', 'needs_reconciliation')->count();

        // Scenario progress
        $passedScenarios = $business->sandboxScenarios()->wherePivot('status', 'passed')->count();
        $totalRequiredScenarios = 3; // e.g. SN003, SN004, SN011 for standard sector
        $remainingScenariosList = ['SN003', 'SN004', 'SN011'];

        // Production sales aggregations (accepted only)
        $monthAccepted = (clone $prodInvoices)->where('status', 'accepted')->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
        $allTimeAccepted = (clone $prodInvoices)->where('status', 'accepted');

        $salesThisMonth = [
            'sales_value_excl_tax' => (float) (clone $monthAccepted)->sum('sales_value_excl_tax'),
            'sales_tax' => (float) (clone $monthAccepted)->sum('sales_tax_amount'),
            'further_tax' => (float) (clone $monthAccepted)->sum('further_tax_amount'),
            'st_withheld' => (float) (clone $monthAccepted)->sum('st_withheld_amount'),
            'gross_value' => (float) (clone $monthAccepted)->sum('total_invoice_value'),
        ];

        $salesAllTime = [
            'sales_value_excl_tax' => (float) (clone $allTimeAccepted)->sum('sales_value_excl_tax'),
            'sales_tax' => (float) (clone $allTimeAccepted)->sum('sales_tax_amount'),
            'further_tax' => (float) (clone $allTimeAccepted)->sum('further_tax_amount'),
            'st_withheld' => (float) (clone $allTimeAccepted)->sum('st_withheld_amount'),
            'gross_value' => (float) (clone $allTimeAccepted)->sum('total_invoice_value'),
        ];

        return [
            'total_production_invoices' => $totalProdCount,
            'sandbox_invoices_count' => $sandboxInvoicesCount,
            'this_month_invoices' => $thisMonthCount,
            'accepted_by_fbr' => $acceptedCount,
            'failed_submissions' => $failedCount,
            'drafts' => $draftsCount,
            'pending_submission' => $pendingCount,
            'need_reconciliation' => $reconcileCount,
            'scenarios_passed_count' => $passedScenarios,
            'scenarios_total_count' => $totalRequiredScenarios,
            'scenarios_remaining' => $remainingScenariosList,
            'sales_this_month' => $salesThisMonth,
            'sales_all_time' => $salesAllTime,
            'sandbox_token_set' => !empty($business->sandbox_token),
            'production_token_set' => !empty($business->production_token),
            'production_submission_enabled' => (bool) $business->production_submissions_enabled,
            'pkt_today_string' => $pktNow->format('d M Y · H:i') . ' PKT',
            'pkt_date_range_string' => $pktNow->copy()->startOfMonth()->format('d M Y') . ' – ' . $pktNow->copy()->endOfMonth()->format('d M Y'),
        ];
    }

    public function getRecentInvoices(Business $business, int $limit = 5)
    {
        return $business->invoices()
            ->with('customer')
            ->latest('invoice_date')
            ->take($limit)
            ->get();
    }

    public function getRecentFbrActivity(Business $business, int $limit = 5)
    {
        return $business->fbrSubmissions()
            ->latest('executed_at')
            ->take($limit)
            ->get();
    }
}

