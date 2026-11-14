<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Contracts\FbrValidationServiceInterface;
use Carbon\Carbon;

class FbrValidationService implements FbrValidationServiceInterface
{
    public function isCurrentMonthInPakistan(string|\DateTimeInterface $date): bool
    {
        $pktNow = Carbon::now('Asia/Karachi');
        $checkDate = Carbon::parse($date, 'Asia/Karachi');

        return $checkDate->year === $pktNow->year && $checkDate->month === $pktNow->month;
    }

    public function validateNtnCnic(string $ntnCnic): bool
    {
        $clean = preg_replace('/\D/', '', $ntnCnic);
        return strlen($clean) === 7 || strlen($clean) === 13;
    }

    public function validateBuyerRegistrationType(Customer $customer, Business $business): array
    {
        $errors = [];
        $cleanNtn = preg_replace('/\D/', '', $customer->ntn_cnic);

        if (!$this->validateNtnCnic($cleanNtn)) {
            $errors[] = "Buyer '{$customer->name}' must have a valid 7-digit NTN or 13-digit CNIC (got '{$customer->ntn_cnic}').";
        }

        if ($customer->isRegistered() && strlen($cleanNtn) === 13) {
            // Under FBR rules, businesses registered for sales tax usually hold a 7-digit NTN
            // but CNIC can also be accepted for individual sole-proprietors.
        }

        return $errors;
    }

    public function calculateItemTotals(array $itemData, bool $isUnregisteredBuyer): array
    {
        $quantity = (float) ($itemData['quantity'] ?? 1);
        $unitPrice = (float) ($itemData['unit_price'] ?? 0);
        $valueExclTax = round($quantity * $unitPrice, 2);

        $fixedOrRetail = (float) ($itemData['fixed_notified_value'] ?? $itemData['retail_price'] ?? 0);
        $taxableBase = ($fixedOrRetail > 0) ? round($fixedOrRetail * $quantity, 2) : $valueExclTax;

        $taxRate = (float) ($itemData['tax_rate'] ?? 18.00);
        $salesTaxAmount = isset($itemData['sales_tax_amount']) && $itemData['sales_tax_amount'] !== ''
            ? round((float) $itemData['sales_tax_amount'], 2)
            : round(($taxableBase * $taxRate) / 100, 2);

        // Under Section 3(1A) of Sales Tax Act 1990, Further Tax (typically 4%) applies to supplies made to unregistered persons
        $isCancelled = !empty($itemData['cancel_further_tax']) || (isset($itemData['further_tax_rate']) && (float)$itemData['further_tax_rate'] === 0.0 && empty($itemData['further_tax_amount']));
        
        if ($isCancelled) {
            $furtherTaxRate = 0.00;
            $furtherTaxAmount = 0.00;
        } elseif (isset($itemData['further_tax_amount']) && (float)$itemData['further_tax_amount'] > 0) {
            $furtherTaxAmount = round((float) $itemData['further_tax_amount'], 2);
            $furtherTaxRate = $taxableBase > 0 ? round(($furtherTaxAmount / $taxableBase) * 100, 2) : 4.00;
        } elseif ($isUnregisteredBuyer) {
            $furtherTaxRate = (float) ($itemData['further_tax_rate'] ?? 4.00);
            $furtherTaxAmount = round(($taxableBase * $furtherTaxRate) / 100, 2);
        } else {
            $furtherTaxRate = 0.00;
            $furtherTaxAmount = 0.00;
        }

        $stWithheldRate = (float) ($itemData['st_withheld_rate'] ?? 0.00);
        $stWithheldAmount = round(($salesTaxAmount * $stWithheldRate) / 100, 2);

        $discountAmount = round((float) ($itemData['discount_amount'] ?? 0.00), 2);
        $extraTaxAmount = round((float) ($itemData['extra_tax_amount'] ?? 0.00), 2);
        $fedPayableAmount = round((float) ($itemData['fed_payable_amount'] ?? 0.00), 2);

        $totalInclTax = round($valueExclTax + $salesTaxAmount + $furtherTaxAmount + $extraTaxAmount + $fedPayableAmount - $discountAmount, 2);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'value_excl_tax' => $valueExclTax,
            'fixed_notified_value' => $fixedOrRetail > 0 ? $fixedOrRetail : null,
            'retail_price' => $fixedOrRetail > 0 ? $fixedOrRetail : null,
            'tax_rate' => $taxRate,
            'sales_tax_amount' => $salesTaxAmount,
            'further_tax_rate' => $furtherTaxRate,
            'further_tax_amount' => $furtherTaxAmount,
            'extra_tax_amount' => $extraTaxAmount,
            'fed_payable_amount' => $fedPayableAmount,
            'st_withheld_rate' => $stWithheldRate,
            'st_withheld_amount' => $stWithheldAmount,
            'discount_amount' => $discountAmount,
            'total_value_incl_tax' => $totalInclTax,
        ];
    }

    public function recalculateInvoiceTotals(Invoice $invoice): Invoice
    {
        $items = $invoice->items;

        $totalExcl = 0.00;
        $totalSalesTax = 0.00;
        $totalFurtherTax = 0.00;
        $totalExtraTax = 0.00;
        $totalFed = 0.00;
        $totalSTWithheld = 0.00;
        $totalDiscount = 0.00;
        $grossTotal = 0.00;

        foreach ($items as $item) {
            $totalExcl += (float) $item->value_excl_tax;
            $totalSalesTax += (float) $item->sales_tax_amount;
            $totalFurtherTax += (float) $item->further_tax_amount;
            $totalExtraTax += (float) ($item->extra_tax_amount ?? 0);
            $totalFed += (float) ($item->fed_payable_amount ?? 0);
            $totalSTWithheld += (float) $item->st_withheld_amount;
            $totalDiscount += (float) $item->discount_amount;
            $grossTotal += (float) $item->total_value_incl_tax;
        }

        $netReceivable = $grossTotal - $totalSTWithheld;

        $invoice->sales_value_excl_tax = round($totalExcl, 2);
        $invoice->sales_tax_amount = round($totalSalesTax, 2);
        $invoice->further_tax_amount = round($totalFurtherTax, 2);
        $invoice->extra_tax_amount = round($totalExtraTax, 2);
        $invoice->fed_amount = round($totalFed, 2);
        $invoice->st_withheld_amount = round($totalSTWithheld, 2);
        $invoice->discount_amount = round($totalDiscount, 2);
        $invoice->total_invoice_value = round($grossTotal, 2);
        $invoice->net_receivable_amount = round($netReceivable, 2);

        $invoice->save();

        return $invoice;
    }

    public function validateInvoicePayload(Invoice $invoice): array
    {
        $errors = [];

        // 1. Current month check in Pakistan Standard Time
        if (!$this->isCurrentMonthInPakistan($invoice->invoice_date)) {
            $pktNow = Carbon::now('Asia/Karachi');
            $startOfMonth = $pktNow->copy()->startOfMonth()->format('d M Y');
            $endOfMonth = $pktNow->copy()->endOfMonth()->format('d M Y');
            $errors[] = "Invoices can only be dated and submitted for the current calendar month in Pakistan Standard Time ({$startOfMonth} – {$endOfMonth}).";
        }

        // 2. Buyer validation
        $customer = $invoice->customer;
        if (!$customer) {
            $errors[] = "Invoice must have an associated customer.";
        } else {
            $buyerErrors = $this->validateBuyerRegistrationType($customer, $invoice->business);
            $errors = array_merge($errors, $buyerErrors);
        }

        // 3. Item validation
        if ($invoice->items->isEmpty()) {
            $errors[] = "Invoice must contain at least one line item.";
        }

        foreach ($invoice->items as $index => $item) {
            $lineNum = $index + 1;
            $cleanHs = preg_replace('/\D/', '', $item->hs_code);
            if (strlen($cleanHs) < 4) {
                $errors[] = "Line item #{$lineNum}: HS/PCT code '{$item->hs_code}' is invalid. Must be at least 4 digits.";
            }
            if ($item->quantity <= 0) {
                $errors[] = "Line item #{$lineNum}: Quantity must be greater than zero.";
            }
            if ($item->unit_price < 0) {
                $errors[] = "Line item #{$lineNum}: Unit price cannot be negative.";
            }
        }

        // 4. Token validation
        $business = $invoice->business;
        if ($invoice->environment === 'production') {
            if (!$business->production_submissions_enabled) {
                $errors[] = "Production submissions are disabled for this business.";
            }
            if (empty($business->production_token)) {
                $errors[] = "Production FBR token is not configured.";
            }
        } else {
            if (empty($business->sandbox_token) && !config('services.fbr.use_simulator', true)) {
                $errors[] = "Sandbox FBR token is not configured.";
            }
        }

        return $errors;
    }
}

