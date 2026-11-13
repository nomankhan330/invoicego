<?php

namespace App\Services\Contracts;

use App\Models\Business;
use App\Models\Invoice;

interface InvoiceServiceInterface
{
    public function createInvoice(array $data, Business $business, int $userId): Invoice;
    public function updateInvoice(Invoice $invoice, array $data): Invoice;
    public function deleteInvoice(Invoice $invoice): bool;
    public function getDashboardMetrics(Business $business): array;
    public function getRecentInvoices(Business $business, int $limit = 5);
    public function getRecentFbrActivity(Business $business, int $limit = 5);
}
