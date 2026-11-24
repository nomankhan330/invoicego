<?php

namespace App\Services\Contracts;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\SandboxScenario;

interface FbrIntegrationServiceInterface
{
    public function submitInvoice(Invoice $invoice): array;
    public function validateWithFbr(Invoice $invoice): array;
    public function verifyConnection(Business $business, string $environment, string $token): array;
    public function validateSandboxPayload(Business $business, string $scenarioCode, array $payload): array;
    public function runSandboxScenario(Business $business, SandboxScenario $scenario, ?array $customPayload = null): array;
    public function parseFbrResponse(int $httpStatus, ?array $json, string $rawBody = ''): array;
    public function checkBuyerRegistration(string $ntnCnic, Business $business): array;
    public function reconcileInvoice(Invoice $invoice): array;
}
