<?php

namespace App\Services\Contracts;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\SandboxScenario;

interface FbrIntegrationServiceInterface
{
    public function submitInvoice(Invoice $invoice): array;
    public function validateWithFbr(Invoice $invoice): array;
    public function runSandboxScenario(Business $business, SandboxScenario $scenario): array;
    public function checkBuyerRegistration(string $ntnCnic, Business $business): array;
    public function reconcileInvoice(Invoice $invoice): array;
}

