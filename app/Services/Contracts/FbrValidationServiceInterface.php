<?php

namespace App\Services\Contracts;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;

interface FbrValidationServiceInterface
{
    public function isCurrentMonthInPakistan(string|\DateTimeInterface $date): bool;
    public function validateNtnCnic(string $ntnCnic): bool;
    public function validateBuyerRegistrationType(Customer $customer, Business $business): array;
    public function calculateItemTotals(array $itemData, bool $isUnregisteredBuyer): array;
    public function recalculateInvoiceTotals(Invoice $invoice): Invoice;
    public function validateInvoicePayload(Invoice $invoice): array;
}

