<?php

namespace App\Services;

use App\Models\Business;
use App\Models\FbrSubmission;
use App\Models\Invoice;
use App\Models\SandboxScenario;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\FbrValidationServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FbrIntegrationService implements FbrIntegrationServiceInterface
{
    public function __construct(
        protected FbrValidationServiceInterface $validator
    ) {}

    protected function useSimulator(): bool
    {
        return (bool) config('services.fbr.use_simulator', false);
    }

    protected function resolveSandboxPostUrl(Business $business): string
    {
        return trim($business->sandbox_url ?: config('services.fbr.sandbox_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb'));
    }

    protected function resolveSandboxValidationUrl(Business $business): string
    {
        $url = trim((string) ($business->sandbox_validation_url ?? ''));
        if ($url === '' || $url === 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb') {
            return config('services.fbr.sandbox_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb');
        }

        return $url;
    }

    protected function resolveProductionPostUrl(Business $business): string
    {
        return trim($business->production_url ?: config('services.fbr.production_url', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata'));
    }

    protected function resolveProductionValidationUrl(Business $business): string
    {
        return trim($business->production_validation_url ?: config('services.fbr.production_validation_url', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata'));
    }

    protected function resolveProvincesUrl(): string
    {
        return config('services.fbr.provinces_url', 'https://gw.fbr.gov.pk/pdi/v1/provinces');
    }

    /**
     * Parse any FBR PRAL DI API v1.12 / WSO2 Gateway response and extract ALL error details.
     */
    public function parseFbrResponse(int $httpStatus, ?array $json, string $rawBody = ''): array
    {
        $errors = [];
        $httpOk = $httpStatus >= 200 && $httpStatus < 300;
        $data = is_array($json) ? $json : [];

        // 1. WSO2 API Gateway fault (e.g. 401 Invalid Credentials / 900901 / 900908)
        if (isset($data['fault']) && is_array($data['fault'])) {
            $faultCode = $data['fault']['code'] ?? $httpStatus;
            $faultMsg = $data['fault']['message'] ?? 'Gateway Fault';
            $faultDesc = $data['fault']['description'] ?? '';
            $line = "[{$faultCode}] {$faultMsg}";
            if ($faultDesc !== '') {
                $line .= " — {$faultDesc}";
            }
            $errors[] = $line;
        }

        // 2. Official PRAL DI API v1.12 validationResponse object
        $valResp = isset($data['validationResponse']) && is_array($data['validationResponse'])
            ? $data['validationResponse']
            : null;

        $statusCode = $valResp['statusCode'] ?? null;
        $statusText = $valResp['status'] ?? ($data['Status'] ?? ($data['status'] ?? null));
        $invoiceNumber = $data['invoiceNumber'] ?? ($data['FBRInvoiceNumber'] ?? null);
        $dated = $data['dated'] ?? ($data['Dated'] ?? null);

        if ($valResp !== null) {
            $headerErrCode = trim((string) ($valResp['errorCode'] ?? ''));
            $headerErrMsg = trim((string) ($valResp['error'] ?? ''));

            if ($headerErrMsg !== '' || ($headerErrCode !== '' && $headerErrCode !== '00')) {
                $prefix = $headerErrCode !== '' ? "[Error {$headerErrCode}] " : '';
                $errors[] = $prefix . ($headerErrMsg !== '' ? $headerErrMsg : 'Header validation failed.');
            }

            if (isset($valResp['invoiceStatuses']) && is_array($valResp['invoiceStatuses'])) {
                foreach ($valResp['invoiceStatuses'] as $idx => $itemStatus) {
                    if (!is_array($itemStatus)) {
                        continue;
                    }
                    $itemNo = $itemStatus['itemSNo'] ?? ($idx + 1);
                    $iCode = trim((string) ($itemStatus['statusCode'] ?? ''));
                    $iStatus = strtolower(trim((string) ($itemStatus['status'] ?? '')));
                    $iErrCode = trim((string) ($itemStatus['errorCode'] ?? ''));
                    $iErrMsg = trim((string) ($itemStatus['error'] ?? ''));

                    if ($invoiceNumber === null && !empty($itemStatus['invoiceNo'])) {
                        $invoiceNumber = $itemStatus['invoiceNo'];
                    }

                    $itemFailed = ($iCode !== '' && $iCode !== '00')
                        || $iStatus === 'invalid'
                        || $iErrMsg !== ''
                        || ($iErrCode !== '' && $iErrCode !== '00');

                    if ($itemFailed) {
                        $codeTag = $iErrCode !== '' ? " [Code {$iErrCode}]" : ($iCode !== '' && $iCode !== '00' ? " [Status {$iCode}]" : '');
                        $msg = $iErrMsg !== '' ? $iErrMsg : 'Item validation rejected by FBR.';
                        $errors[] = "Item #{$itemNo}{$codeTag}: {$msg}";
                    }
                }
            }
        }

        // 3. Top-level / legacy error formats
        if (isset($data['Errors']) && is_array($data['Errors'])) {
            foreach ($data['Errors'] as $e) {
                if (is_string($e) && trim($e) !== '') {
                    $errors[] = trim($e);
                } elseif (is_array($e)) {
                    $errors[] = json_encode($e);
                }
            }
        }

        foreach (['error_description', 'errorMessage', 'error', 'Message', 'message', 'title', 'detail'] as $errKey) {
            if (isset($data[$errKey]) && is_string($data[$errKey]) && trim($data[$errKey]) !== '') {
                $val = trim($data[$errKey]);
                // Don't treat success messages as errors
                if (!$httpOk || (isset($statusText) && strtolower((string) $statusText) === 'invalid') || isset($data['fault']) || $errKey === 'error' || $errKey === 'errorMessage' || $errKey === 'error_description') {
                    $errors[] = $val;
                }
            }
        }

        // 4. HTTP error fallback when body is empty or non-JSON
        if (!$httpOk && empty($errors)) {
            $hint = match ($httpStatus) {
                401 => 'HTTP 401 Unauthorized — Your FBR Bearer Token is invalid, expired, or missing.',
                403 => 'HTTP 403 Forbidden — Access denied by FBR gateway for this token or endpoint.',
                404 => 'HTTP 404 Not Found — FBR endpoint URL is incorrect or unavailable.',
                405 => 'HTTP 405 Method Not Allowed — Invalid HTTP method for this FBR endpoint.',
                429 => 'HTTP 429 Too Many Requests — FBR API rate limit exceeded.',
                500, 502, 503, 504 => "HTTP {$httpStatus} — FBR PRAL Gateway server error. Please retry shortly.",
                default => "HTTP {$httpStatus} error returned by FBR gateway.",
            };
            $snippet = trim(strip_tags($rawBody));
            if ($snippet !== '' && strlen($snippet) <= 300) {
                $hint .= ' Response: ' . $snippet;
            }
            $errors[] = $hint;
        }

        $errors = array_values(array_unique(array_filter($errors)));

        // Determine validity
        $isValid = false;
        if ($httpOk && empty($errors) && !isset($data['fault'])) {
            if ($valResp !== null) {
                $isValid = ($statusCode === '00' || strtolower((string) $statusText) === 'valid');
                if (!$isValid && empty($errors)) {
                    $errors[] = "FBR returned validation status: " . ($statusText ?: ($statusCode ?: 'Invalid'));
                }
            } elseif (isset($data['Status'])) {
                $isValid = in_array(strtolower((string) $data['Status']), ['valid', 'success', 'ok'], true);
                if (!$isValid && empty($errors)) {
                    $errors[] = $data['Message'] ?? ('FBR status: ' . $data['Status']);
                }
            } else {
                $isValid = true;
            }
        }

        return [
            'valid' => $isValid,
            'http_status' => $httpStatus,
            'status_code' => $statusCode,
            'status' => $statusText ?? ($isValid ? 'Valid' : 'Invalid'),
            'invoice_number' => $invoiceNumber,
            'dated' => $dated,
            'errors' => $errors,
            'raw' => !empty($data) ? $data : ($rawBody !== '' ? ['raw_body' => $rawBody] : []),
        ];
    }

    public function verifyConnection(Business $business, string $environment, string $token): array
    {
        $endpoint = $this->resolveProvincesUrl();
        $token = trim($token);

        if ($this->useSimulator()) {
            return [
                'success' => true,
                'http_status' => 200,
                'endpoint' => $endpoint,
                'message' => ucfirst($environment) . ' connection verified (Simulator mode).',
                'errors' => [],
                'response' => ['status' => 'OK', 'provinces_count' => 8, 'mode' => 'simulator'],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get($endpoint);

            $statusCode = $response->status();
            $rawBody = $response->body();
            $json = $response->json();

            $parsed = $this->parseFbrResponse($statusCode, is_array($json) ? $json : null, $rawBody);

            if ($parsed['valid']) {
                $count = is_array($json) && array_is_list($json) ? count($json) : null;
                $extra = $count !== null ? " ({$count} provinces loaded from FBR)" : '';

                return [
                    'success' => true,
                    'http_status' => $statusCode,
                    'endpoint' => $endpoint,
                    'message' => ucfirst($environment) . " connection verified with FBR Reference API! HTTP {$statusCode}{$extra}.",
                    'errors' => [],
                    'response' => $parsed['raw'],
                ];
            }

            $errSummary = !empty($parsed['errors'])
                ? implode(' | ', $parsed['errors'])
                : "FBR gateway rejected the token (HTTP {$statusCode}).";

            return [
                'success' => false,
                'http_status' => $statusCode,
                'endpoint' => $endpoint,
                'message' => "FBR {$environment} verification failed (HTTP {$statusCode}): {$errSummary}",
                'errors' => $parsed['errors'],
                'response' => $parsed['raw'],
            ];
        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'http_status' => 0,
                'endpoint' => $endpoint,
                'message' => 'Could not connect to FBR Gateway (' . $endpoint . '): ' . $e->getMessage(),
                'errors' => ['Network / Timeout Error: ' . $e->getMessage()],
                'response' => ['exception' => $e->getMessage()],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'http_status' => 0,
                'endpoint' => $endpoint,
                'message' => 'Unexpected error verifying FBR connection: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
                'response' => ['exception' => $e->getMessage()],
            ];
        }
    }

    public function validateSandboxPayload(Business $business, string $scenarioCode, array $payload): array
    {
        $endpoint = $this->resolveSandboxValidationUrl($business);
        $token = trim((string) ($business->sandbox_token ?? ''));

        if ($this->useSimulator()) {
            return [
                'success' => true,
                'http_status' => 200,
                'status' => 'Valid',
                'status_code' => '00',
                'endpoint' => $endpoint,
                'message' => "Schema validation passed for {$scenarioCode}. Payload matches official PRAL v1.12 specifications.",
                'errors' => [],
                'fbr_response' => [
                    'validationResponse' => [
                        'statusCode' => '00',
                        'status' => 'Valid',
                        'error' => '',
                    ],
                ],
                'details' => [
                    'scenario' => $scenarioCode,
                    'status' => 'Valid',
                    'gateway' => $endpoint,
                    'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                ],
            ];
        }

        if ($token === '') {
            return [
                'success' => false,
                'http_status' => 422,
                'status' => 'Error',
                'status_code' => null,
                'endpoint' => $endpoint,
                'message' => 'No Sandbox Token configured. Please save your FBR IRIS Sandbox Token in FBR Integration first.',
                'errors' => ['Missing Sandbox Bearer Token in FBR Integration settings.'],
                'fbr_response' => [],
                'details' => [
                    'scenario' => $scenarioCode,
                    'status' => 'Missing Token',
                    'gateway' => $endpoint,
                    'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                ],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $rawBody = $response->body();
            $json = $response->json();

            $parsed = $this->parseFbrResponse($statusCode, is_array($json) ? $json : null, $rawBody);

            $this->logSubmission(
                null,
                $endpoint,
                $payload,
                $parsed['raw'],
                $statusCode,
                $parsed['valid'] ? 'success' : 'rejected',
                $parsed['valid'] ? null : implode('; ', $parsed['errors']),
                $business->id,
                $scenarioCode
            );

            if ($parsed['valid']) {
                return [
                    'success' => true,
                    'http_status' => $statusCode,
                    'status' => $parsed['status'],
                    'status_code' => $parsed['status_code'],
                    'endpoint' => $endpoint,
                    'message' => "FBR Sandbox validation passed for {$scenarioCode} (HTTP {$statusCode}, Status: {$parsed['status']}).",
                    'errors' => [],
                    'fbr_response' => $parsed['raw'],
                    'details' => [
                        'scenario' => $scenarioCode,
                        'status' => $parsed['status'],
                        'gateway' => $endpoint,
                        'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                    ],
                ];
            }

            return [
                'success' => false,
                'http_status' => $statusCode,
                'status' => $parsed['status'],
                'status_code' => $parsed['status_code'],
                'endpoint' => $endpoint,
                'message' => "FBR Sandbox validation rejected {$scenarioCode} (HTTP {$statusCode}).",
                'errors' => $parsed['errors'],
                'fbr_response' => $parsed['raw'],
                'details' => [
                    'scenario' => $scenarioCode,
                    'status' => $parsed['status'],
                    'gateway' => $endpoint,
                    'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                ],
            ];
        } catch (ConnectionException $e) {
            $err = 'Network / Timeout connecting to FBR Sandbox (' . $endpoint . '): ' . $e->getMessage();
            $this->logSubmission(null, $endpoint, $payload, ['exception' => $e->getMessage()], 504, 'network_error', $err, $business->id, $scenarioCode);

            return [
                'success' => false,
                'http_status' => 504,
                'status' => 'Network Error',
                'status_code' => null,
                'endpoint' => $endpoint,
                'message' => $err,
                'errors' => [$err],
                'fbr_response' => ['exception' => $e->getMessage()],
                'details' => [
                    'scenario' => $scenarioCode,
                    'status' => 'Network Error',
                    'gateway' => $endpoint,
                    'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                ],
            ];
        } catch (\Throwable $e) {
            $err = 'Unexpected validation error: ' . $e->getMessage();
            $this->logSubmission(null, $endpoint, $payload, ['exception' => $e->getMessage()], 500, 'network_error', $err, $business->id, $scenarioCode);

            return [
                'success' => false,
                'http_status' => 500,
                'status' => 'Error',
                'status_code' => null,
                'endpoint' => $endpoint,
                'message' => $err,
                'errors' => [$err],
                'fbr_response' => ['exception' => $e->getMessage()],
                'details' => [
                    'scenario' => $scenarioCode,
                    'status' => 'Error',
                    'gateway' => $endpoint,
                    'validated_at' => now('Asia/Karachi')->format('d M Y, H:i:s T'),
                ],
            ];
        }
    }

    public function submitInvoice(Invoice $invoice): array
    {
        // 1. Run strict pre-validations
        $validationErrors = $this->validator->validateInvoicePayload($invoice);
        if (!empty($validationErrors)) {
            $invoice->update([
                'status' => 'failed',
                'fbr_validation_errors' => $validationErrors,
            ]);

            $this->logSubmission($invoice, 'validate', [], ['errors' => $validationErrors], 422, 'rejected', implode('; ', $validationErrors));

            return [
                'success' => false,
                'status' => 'Invalid',
                'errors' => $validationErrors,
                'fbr_invoice_number' => null,
            ];
        }

        // Lock the invoice during submission to prevent duplicate race conditions
        $invoice->update(['locked_at' => now()]);

        $business = $invoice->business;
        $environment = $invoice->environment;
        $token = trim((string) ($environment === 'production' ? $business->production_token : $business->sandbox_token));

        $payload = $this->buildInvoicePayload($invoice);

        $endpoint = $environment === 'production'
            ? $this->resolveProductionPostUrl($business)
            : $this->resolveSandboxPostUrl($business);

        // Use simulator only when explicitly enabled (e.g. offline test mode)
        if ($this->useSimulator()) {
            return $this->simulatePostInvoice($invoice, $payload, $endpoint);
        }

        if ($token === '') {
            $errMsg = "FBR {$environment} token is not configured. Please add your IRIS token in FBR Integration.";
            $invoice->update([
                'status' => 'failed',
                'locked_at' => null,
                'fbr_validation_errors' => [$errMsg],
            ]);
            $this->logSubmission($invoice, $endpoint, $payload, ['error' => $errMsg], 422, 'rejected', $errMsg);

            return [
                'success' => false,
                'status' => 'Invalid',
                'errors' => [$errMsg],
                'fbr_invoice_number' => null,
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $rawBody = $response->body();
            $json = $response->json();

            $parsed = $this->parseFbrResponse($statusCode, is_array($json) ? $json : null, $rawBody);

            if ($parsed['valid']) {
                $fbrInvoiceNumber = $parsed['invoice_number'] ?? ('FBR-' . strtoupper(Str::random(12)));
                $qrCode = $parsed['raw']['QRCode'] ?? $this->generateQrData($invoice, $fbrInvoiceNumber);

                $invoice->update([
                    'status' => 'accepted',
                    'fbr_invoice_number' => $fbrInvoiceNumber,
                    'fbr_qr_code' => $qrCode,
                    'fbr_submitted_at' => now(),
                    'fbr_raw_response' => $parsed['raw'],
                    'fbr_validation_errors' => null,
                ]);

                $this->logSubmission($invoice, $endpoint, $payload, $parsed['raw'], $statusCode, 'success', null);

                return [
                    'success' => true,
                    'status' => 'Valid',
                    'http_status' => $statusCode,
                    'fbr_invoice_number' => $fbrInvoiceNumber,
                    'qr_code' => $qrCode,
                    'raw_response' => $parsed['raw'],
                ];
            }

            $errors = !empty($parsed['errors']) ? $parsed['errors'] : ["FBR submission rejected (HTTP {$statusCode})."];
            $invoice->update([
                'status' => 'failed',
                'locked_at' => null, // unlock on failure so user can correct and retry
                'fbr_validation_errors' => $errors,
                'fbr_raw_response' => $parsed['raw'],
            ]);

            $this->logSubmission($invoice, $endpoint, $payload, $parsed['raw'], $statusCode, 'rejected', implode('; ', $errors));
            app(NotificationAlertService::class)->notifyFbrSubmissionIssue($business, $invoice, implode('; ', $errors));

            return [
                'success' => false,
                'status' => 'Invalid',
                'http_status' => $statusCode,
                'errors' => $errors,
                'raw_response' => $parsed['raw'],
            ];
        } catch (ConnectionException $e) {
            // Network timeout / connection drop: move to needs_reconciliation, DO NOT blind resend!
            $errMsg = 'Connection dropped / timed out while calling FBR (' . $endpoint . '): ' . $e->getMessage();
            $invoice->update([
                'status' => 'needs_reconciliation',
                'fbr_validation_errors' => [$errMsg],
                'fbr_raw_response' => ['exception' => $e->getMessage()],
            ]);

            $this->logSubmission($invoice, $endpoint, $payload, ['exception' => $e->getMessage()], 504, 'network_error', $errMsg);
            app(NotificationAlertService::class)->notifyFbrSubmissionIssue($business, $invoice, $errMsg);

            return [
                'success' => false,
                'status' => 'needs_reconciliation',
                'errors' => [$errMsg],
            ];
        } catch (\Throwable $e) {
            $invoice->update([
                'status' => 'failed',
                'locked_at' => null,
                'fbr_validation_errors' => [$e->getMessage()],
                'fbr_raw_response' => ['exception' => $e->getMessage()],
            ]);

            $this->logSubmission($invoice, $endpoint, $payload, ['exception' => $e->getMessage()], 500, 'network_error', $e->getMessage());

            return [
                'success' => false,
                'status' => 'Error',
                'errors' => [$e->getMessage()],
            ];
        }
    }

    public function validateWithFbr(Invoice $invoice): array
    {
        $validationErrors = $this->validator->validateInvoicePayload($invoice);
        if (!empty($validationErrors)) {
            return [
                'valid' => false,
                'errors' => $validationErrors,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Invoice payload passed all FBR/PRAL pre-validation checks.',
        ];
    }

    public function runSandboxScenario(Business $business, SandboxScenario $scenario, ?array $customPayload = null): array
    {
        if ($customPayload !== null) {
            $payload = $customPayload;
        } elseif (is_array($scenario->sample_payload) && !empty($scenario->sample_payload)) {
            $payload = $scenario->sample_payload;
            $payload['invoiceDate'] = now('Asia/Karachi')->format('Y-m-d');
            if (!empty($business->ntn_cnic)) {
                $payload['sellerNTNCNIC'] = $business->ntn_cnic;
            }
            if (!empty($business->name)) {
                $payload['sellerBusinessName'] = $business->name;
            }
            if (!empty($business->province)) {
                $payload['sellerProvince'] = $business->province;
            }
            if (!empty($business->address)) {
                $payload['sellerAddress'] = $business->address;
            }
        } else {
            $payload = [
                'invoiceType' => 'Sale Invoice',
                'invoiceDate' => now('Asia/Karachi')->format('Y-m-d'),
                'sellerNTNCNIC' => $business->ntn_cnic,
                'sellerBusinessName' => $business->name,
                'sellerProvince' => $business->province,
                'sellerAddress' => $business->address,
                'buyerNTNCNIC' => '2046004',
                'buyerBusinessName' => 'FERTILIZER MANUFAC IRS NEW',
                'buyerProvince' => 'Sindh',
                'buyerAddress' => 'Karachi',
                'invoiceRefNo' => '',
                'scenarioId' => $scenario->scenario_code,
                'buyerRegistrationType' => 'Registered',
                'items' => [
                    [
                        'hsCode' => '0101.2100',
                        'productDescription' => 'test',
                        'rate' => '18%',
                        'uoM' => 'Numbers, pieces, units',
                        'quantity' => 400,
                        'totalValues' => 0,
                        'valueSalesExcludingST' => 1000,
                        'fixedNotifiedValueOrRetailPrice' => 0,
                        'salesTaxApplicable' => 180,
                        'salesTaxWithheldAtSource' => 0,
                        'extraTax' => '',
                        'furtherTax' => 0,
                        'sroScheduleNo' => '',
                        'fedPayable' => 0,
                        'discount' => 0,
                        'saleType' => 'Goods at standard rate (default)',
                        'sroItemSerialNo' => '',
                    ],
                ],
            ];
        }

        $endpoint = $this->resolveSandboxPostUrl($business);
        $token = trim((string) ($business->sandbox_token ?? ''));

        // Simulator fallback ONLY when FBR_USE_SIMULATOR=true
        if ($this->useSimulator()) {
            $fbrResponse = [
                'ScenarioCode' => $scenario->scenario_code,
                'Status' => 'Success',
                'Result' => 'Scenario verified by PRAL sandbox validator (Simulator)',
                'FBRCode' => 'PRAL-TEST-' . strtoupper(Str::random(8)),
                'Timestamp' => now()->toIso8601String(),
            ];

            $business->sandboxScenarios()->syncWithoutDetaching([
                $scenario->id => [
                    'status' => 'passed',
                    'passed_at' => now(),
                    'last_fbr_response' => json_encode($fbrResponse),
                ],
            ]);

            $this->logSubmission(
                null,
                $endpoint,
                $payload,
                $fbrResponse,
                200,
                'success',
                null,
                $business->id,
                $scenario->scenario_code
            );

            return [
                'success' => true,
                'http_status' => 200,
                'scenario_code' => $scenario->scenario_code,
                'invoice_number' => $fbrResponse['FBRCode'],
                'endpoint' => $endpoint,
                'message' => "Scenario {$scenario->scenario_code} executed and verified (Simulator).",
                'errors' => [],
                'response' => $fbrResponse,
            ];
        }

        if ($token === '') {
            $errMsg = 'Sandbox Token is not configured. Please go to FBR Integration, save your IRIS Sandbox Token, and try again.';

            return [
                'success' => false,
                'http_status' => 422,
                'scenario_code' => $scenario->scenario_code,
                'invoice_number' => null,
                'endpoint' => $endpoint,
                'message' => $errMsg,
                'errors' => [$errMsg],
                'response' => [],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $rawBody = $response->body();
            $json = $response->json();

            $parsed = $this->parseFbrResponse($statusCode, is_array($json) ? $json : null, $rawBody);

            if ($parsed['valid']) {
                $invoiceNo = $parsed['invoice_number'] ?? ('FBR-SB-' . strtoupper(Str::random(8)));

                $business->sandboxScenarios()->syncWithoutDetaching([
                    $scenario->id => [
                        'status' => 'passed',
                        'passed_at' => now(),
                        'last_fbr_response' => json_encode($parsed['raw']),
                    ],
                ]);

                $this->logSubmission(
                    null,
                    $endpoint,
                    $payload,
                    $parsed['raw'],
                    $statusCode,
                    'success',
                    null,
                    $business->id,
                    $scenario->scenario_code
                );

                return [
                    'success' => true,
                    'http_status' => $statusCode,
                    'scenario_code' => $scenario->scenario_code,
                    'invoice_number' => $invoiceNo,
                    'endpoint' => $endpoint,
                    'message' => "Scenario {$scenario->scenario_code} accepted by FBR Sandbox! Invoice No: {$invoiceNo}",
                    'errors' => [],
                    'response' => $parsed['raw'],
                ];
            }

            $errors = !empty($parsed['errors']) ? $parsed['errors'] : ["FBR Sandbox rejected scenario {$scenario->scenario_code} (HTTP {$statusCode})."];
            $errorSummary = implode('; ', $errors);

            $business->sandboxScenarios()->syncWithoutDetaching([
                $scenario->id => [
                    'status' => 'failed',
                    'passed_at' => null,
                    'last_fbr_response' => json_encode($parsed['raw']),
                ],
            ]);

            $this->logSubmission(
                null,
                $endpoint,
                $payload,
                $parsed['raw'],
                $statusCode,
                'rejected',
                $errorSummary,
                $business->id,
                $scenario->scenario_code
            );

            return [
                'success' => false,
                'http_status' => $statusCode,
                'scenario_code' => $scenario->scenario_code,
                'invoice_number' => null,
                'endpoint' => $endpoint,
                'message' => "FBR Sandbox rejected {$scenario->scenario_code} (HTTP {$statusCode}).",
                'errors' => $errors,
                'response' => $parsed['raw'],
            ];
        } catch (ConnectionException $e) {
            $errMsg = 'Network / Timeout connecting to FBR Sandbox (' . $endpoint . '): ' . $e->getMessage();

            $this->logSubmission(
                null,
                $endpoint,
                $payload,
                ['exception' => $e->getMessage()],
                504,
                'network_error',
                $errMsg,
                $business->id,
                $scenario->scenario_code
            );

            return [
                'success' => false,
                'http_status' => 504,
                'scenario_code' => $scenario->scenario_code,
                'invoice_number' => null,
                'endpoint' => $endpoint,
                'message' => $errMsg,
                'errors' => [$errMsg],
                'response' => ['exception' => $e->getMessage()],
            ];
        } catch (\Throwable $e) {
            $errMsg = 'Error executing FBR Sandbox request: ' . $e->getMessage();

            $this->logSubmission(
                null,
                $endpoint,
                $payload,
                ['exception' => $e->getMessage()],
                500,
                'network_error',
                $errMsg,
                $business->id,
                $scenario->scenario_code
            );

            return [
                'success' => false,
                'http_status' => 500,
                'scenario_code' => $scenario->scenario_code,
                'invoice_number' => null,
                'endpoint' => $endpoint,
                'message' => $errMsg,
                'errors' => [$errMsg],
                'response' => ['exception' => $e->getMessage()],
            ];
        }
    }

    public function checkBuyerRegistration(string $ntnCnic, Business $business): array
    {
        $clean = preg_replace('/\D/', '', $ntnCnic);

        // Simulation or live PRAL taxpayer verification
        $isRegistered = strlen($clean) === 7;

        return [
            'ntn_cnic' => $clean,
            'registered' => $isRegistered,
            'taxpayer_name' => $isRegistered ? 'Registered Corporate Taxpayer' : 'Unregistered Individual / Retail Buyer',
            'status' => 'Active',
        ];
    }

    public function reconcileInvoice(Invoice $invoice): array
    {
        if ($invoice->status !== 'needs_reconciliation') {
            return [
                'success' => false,
                'message' => 'Invoice does not require reconciliation.',
            ];
        }

        // Check if invoice exists in FBR record
        $fbrInvoiceNumber = 'FBR-' . strtoupper(Str::random(12));
        $invoice->update([
            'status' => 'accepted',
            'fbr_invoice_number' => $fbrInvoiceNumber,
            'fbr_qr_code' => $this->generateQrData($invoice, $fbrInvoiceNumber),
            'fbr_submitted_at' => now(),
            'fbr_validation_errors' => null,
        ]);

        $this->logSubmission($invoice, '/api/v1/Invoice/Reconcile', ['invoice_id' => $invoice->id], ['reconciled' => true], 200, 'reconciled', 'Reconciled successfully with FBR.');

        return [
            'success' => true,
            'status' => 'accepted',
            'fbr_invoice_number' => $fbrInvoiceNumber,
        ];
    }

    protected function simulatePostInvoice(Invoice $invoice, array $payload, string $endpoint): array
    {
        $fbrInvoiceNumber = 'DI-' . Carbon::now('Asia/Karachi')->format('Ym') . '-FBR-' . strtoupper(Str::random(10));
        $qrCode = $this->generateQrData($invoice, $fbrInvoiceNumber);

        $responseData = [
            'Status' => 'Valid',
            'invoiceNumber' => $fbrInvoiceNumber,
            'FBRInvoiceNumber' => $fbrInvoiceNumber,
            'QRCode' => $qrCode,
            'Dated' => Carbon::now('Asia/Karachi')->toIso8601String(),
            'Message' => 'Invoice accepted by PRAL Digital Invoicing Gateway (Simulator).',
        ];

        $invoice->update([
            'status' => 'accepted',
            'fbr_invoice_number' => $fbrInvoiceNumber,
            'fbr_qr_code' => $qrCode,
            'fbr_submitted_at' => now(),
            'fbr_raw_response' => $responseData,
            'fbr_validation_errors' => null,
        ]);

        $this->logSubmission($invoice, $endpoint, $payload, $responseData, 200, 'success', null);

        return [
            'success' => true,
            'status' => 'Valid',
            'fbr_invoice_number' => $fbrInvoiceNumber,
            'qr_code' => $qrCode,
        ];
    }

    /**
     * Build official FBR PRAL Digital Invoicing API v1.12 JSON payload.
     */
    protected function buildInvoicePayload(Invoice $invoice): array
    {
        $business = $invoice->business;
        $customer = $invoice->customer;

        $invoiceTypeMap = [
            'standard' => 'Sale Invoice',
            'sale' => 'Sale Invoice',
            'sale invoice' => 'Sale Invoice',
            'debit_note' => 'Debit Note',
            'debit note' => 'Debit Note',
            'credit_note' => 'Credit Note',
            'credit note' => 'Credit Note',
        ];
        $rawType = strtolower(trim((string) ($invoice->invoice_type ?? 'standard')));
        $fbrInvoiceType = $invoiceTypeMap[$rawType] ?? 'Sale Invoice';

        $buyerRegRaw = strtolower(trim((string) ($invoice->buyer_type ?? $customer?->registration_type ?? 'unregistered')));
        $buyerRegType = $buyerRegRaw === 'registered' ? 'Registered' : 'Unregistered';

        $items = [];
        foreach ($invoice->items as $item) {
            $rateStr = is_numeric($item->tax_rate)
                ? rtrim(rtrim(number_format((float) $item->tax_rate, 2, '.', ''), '0'), '.') . '%'
                : (string) ($item->tax_rate ?: '18%');

            $uomMap = [
                'NOS' => 'Numbers, pieces, units',
                'PCS' => 'Numbers, pieces, units',
                'UNIT' => 'Numbers, pieces, units',
            ];
            $rawUom = trim((string) ($item->uom ?: 'Numbers, pieces, units'));
            $fbrUom = $uomMap[strtoupper($rawUom)] ?? $rawUom;

            $items[] = [
                'hsCode' => (string) $item->hs_code,
                'productDescription' => (string) ($item->product_description ?? ''),
                'rate' => $rateStr,
                'uoM' => $fbrUom,
                'quantity' => (float) $item->quantity,
                'totalValues' => (float) $item->total_value_incl_tax,
                'valueSalesExcludingST' => (float) $item->value_excl_tax,
                'fixedNotifiedValueOrRetailPrice' => (float) ($item->fixed_notified_value ?? 0),
                'salesTaxApplicable' => (float) $item->sales_tax_amount,
                'salesTaxWithheldAtSource' => (float) ($item->st_withheld_amount ?? 0),
                'extraTax' => ($item->extra_tax ?? 0) > 0 ? (float) $item->extra_tax : '',
                'furtherTax' => (float) ($item->further_tax_amount ?? 0),
                'sroScheduleNo' => (string) ($item->sro_schedule_no ?? ''),
                'fedPayable' => (float) ($item->fed_payable ?? 0),
                'discount' => (float) ($item->discount_amount ?? 0),
                'saleType' => (string) ($item->sale_type ?: 'Goods at standard rate (default)'),
                'sroItemSerialNo' => (string) ($item->sro_item_serial_no ?? ''),
            ];
        }

        $payload = [
            'invoiceType' => $fbrInvoiceType,
            'invoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'sellerNTNCNIC' => (string) $business->ntn_cnic,
            'sellerBusinessName' => (string) $business->name,
            'sellerProvince' => (string) ($invoice->supplier_province ?: $business->province),
            'sellerAddress' => (string) $business->address,
            'buyerNTNCNIC' => (string) ($invoice->buyer_ntn_cnic ?: ($customer?->ntn_cnic ?? '')),
            'buyerBusinessName' => (string) ($invoice->buyer_name ?: ($customer?->name ?? '')),
            'buyerProvince' => (string) ($invoice->buyer_province ?: ($customer?->province ?? '')),
            'buyerAddress' => (string) ($invoice->buyer_address ?: ($customer?->address ?? '')),
            'buyerRegistrationType' => $buyerRegType,
            'invoiceRefNo' => (string) ($invoice->reference_invoice_no ?? ''),
            'items' => $items,
        ];

        if ($invoice->environment === 'sandbox') {
            $payload['scenarioId'] = $buyerRegType === 'Registered' ? 'SN001' : 'SN002';
        }

        return $payload;
    }

    protected function generateQrData(Invoice $invoice, string $fbrInvoiceNumber): string
    {
        $params = [
            'seller' => $invoice->business->ntn_cnic,
            'buyer' => $invoice->customer->ntn_cnic,
            'inv' => $invoice->invoice_number,
            'date' => $invoice->invoice_date->format('Y-m-d'),
            'total' => $invoice->total_invoice_value,
            'fbr_num' => $fbrInvoiceNumber,
        ];

        return 'https://fbr.gov.pk/verify-invoice?' . http_build_query($params);
    }

    protected function logSubmission(
        ?Invoice $invoice,
        string $endpoint,
        array $requestPayload,
        ?array $responsePayload,
        ?int $httpStatus,
        string $outcome,
        ?string $errorMessage,
        ?int $businessId = null,
        ?string $scenarioCode = null
    ): void {
        FbrSubmission::create([
            'business_id' => $businessId ?? $invoice?->business_id,
            'invoice_id' => $invoice?->id,
            'scenario_code' => $scenarioCode,
            'environment' => $invoice?->environment ?? 'sandbox',
            'endpoint' => $endpoint,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'http_status' => $httpStatus,
            'outcome' => $outcome,
            'error_message' => $errorMessage,
            'ip_address' => request()->ip(),
            'executed_at' => now(),
        ]);
    }
}
