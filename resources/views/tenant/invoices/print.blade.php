<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FBR Tax Invoice {{ $invoice->invoice_number }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0F172A;
            background: #F1F5F9;
            padding: 24px 0;
            font-size: 12px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 18mm;
            margin: 0 auto;
            background: #FFFFFF;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            box-sizing: border-box;
            border-radius: 4px;
        }
        .fbr-header-pill {
            background-color: #0F3B82;
            color: #FFFFFF;
            padding: 6px 18px;
            font-weight: 700;
            font-size: 12.5px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 4px;
            display: inline-block;
        }
        .fbr-number-badge {
            border: 2px solid #22C55E;
            border-radius: 6px;
            padding: 6px 14px;
            background: #FFFFFF;
            display: inline-block;
            text-align: center;
        }
        .fbr-qr-box {
            border: 2px solid #22C55E;
            border-radius: 8px;
            padding: 12px;
            background: #FFFFFF;
            width: 235px;
            display: inline-block;
        }
        .watermark-sandbox {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 52px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.08);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            pointer-events: none;
            white-space: nowrap;
            z-index: 10;
        }
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
            .a4-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 12mm 12mm !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="container no-print mb-4 text-center">
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="background-color: #0F3B82; border-color: #0F3B82;">
            <i class="bi bi-printer me-1"></i> Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2 ms-2">
            Close
        </button>
    </div>

    <!-- Official A4 Document Container -->
    <div class="a4-page">
        @if ($invoice->environment === 'sandbox')
            <div class="watermark-sandbox">SANDBOX TEST INVOICE</div>
        @endif

        <!-- Document Top Header -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <!-- Brand / Logo -->
            <div class="d-flex align-items-center">
                <div class="me-2" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0F3B82 0%, #16A34A 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #FFF; font-weight: 900; font-size: 20px;">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color: #0F3B82; letter-spacing: -0.5px; font-size: 19px;">
                        DIGITAL <span style="color: #64748B; font-weight: 400;">INVOICING</span>
                    </h4>
                </div>
            </div>

            <!-- Header Badge & Invoice Meta -->
            <div class="text-end">
                <div class="fbr-header-pill mb-2">
                    FBR TAX INVOICE
                </div>
                <div class="small fw-semibold text-dark">
                    Invoice #: <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="small text-secondary">
                    Date: <span class="fw-semibold">{{ $invoice->invoice_date->format('d-M-Y') }}</span>
                </div>

                @if (!empty($invoice->fbr_invoice_number))
                    <div class="fbr-number-badge mt-2">
                        <div style="font-size: 9px; font-weight: 800; color: #16A34A; text-transform: uppercase; letter-spacing: 0.5px;">FBR INVOICE NUMBER</div>
                        <div style="font-size: 13.5px; font-weight: 800; font-family: monospace; color: #15803D; letter-spacing: 0.5px;">{{ $invoice->fbr_invoice_number }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Blue Divider Line -->
        <div style="height: 2px; background-color: #0F3B82; margin: 15px 0 20px 0;"></div>

        <!-- Split Information Cards: Seller & Buyer -->
        <div class="row g-3 mb-4">
            <!-- Seller Information Card -->
            <div class="col-6">
                <div class="card h-100 p-3" style="border: 1px solid #E2E8F0; border-radius: 6px; background-color: #FFFFFF;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #0F3B82; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        SELLER INFORMATION
                    </div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px;">
                        {{ $business->name }}
                    </div>
                    <div class="small text-secondary" style="font-size: 11.5px; line-height: 1.6;">
                        <div><strong>CNIC:</strong> <span class="text-dark">{{ $business->ntn_cnic }}</span></div>
                        <div><strong>STRN:</strong> <span class="text-dark">{{ $business->strn ?? 'N/A' }}</span></div>
                        <div><strong>Province:</strong> <span class="text-dark">{{ $business->province }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Buyer Information Card -->
            <div class="col-6">
                <div class="card h-100 p-3" style="border: 1px solid #E2E8F0; border-radius: 6px; background-color: #FFFFFF;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #0F3B82; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        BUYER INFORMATION
                    </div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px;">
                        {{ $invoice->customer->name }}
                    </div>
                    <div class="small text-secondary" style="font-size: 11.5px; line-height: 1.6;">
                        <div><strong>CNIC:</strong> <span class="text-dark">{{ $invoice->customer->ntn_cnic }}</span></div>
                        <div><strong>Address:</strong> <span class="text-dark">{{ $invoice->customer->address ?? 'N/A' }}</span></div>
                        <div><strong>Province:</strong> <span class="text-dark">{{ $invoice->customer->province ?? 'N/A' }}</span></div>
                        <div>
                            <strong>Status:</strong>
                            <span style="font-weight: 700; color: {{ $invoice->customer->isRegistered() ? '#16A34A' : '#DC2626' }};">
                                {{ $invoice->customer->isRegistered() ? 'Registered' : 'Unregistered' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table (Two-tier Design Matching Reference) -->
        <table class="table table-bordered mb-4" style="border-color: #CBD5E1; vertical-align: middle;">
            <thead style="background-color: #0F3B82; color: #FFFFFF;">
                <tr style="font-size: 11px;">
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 4%; text-align: center; font-weight: 600;">Sr</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 12%; font-weight: 600;">HS Code</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 28%; font-weight: 600;">Description</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 6%; text-align: center; font-weight: 600;">Qty</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 10%; text-align: center; font-weight: 600;">UOM</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 13%; text-align: end; font-weight: 600;">Per Unit Price</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 13%; text-align: end; font-weight: 600;">Sub Total</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 6%; text-align: center; font-weight: 600;">Discount</th>
                    <th style="background-color: #0F3B82; color: #FFFFFF; width: 14%; text-align: end; font-weight: 600;">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $idx => $item)
                    <!-- Row 1: Item Basic Details -->
                    <tr style="font-size: 11.5px; background-color: #FFFFFF;">
                        <td class="text-center text-secondary">{{ $idx + 1 }}</td>
                        <td class="font-monospace text-dark fw-semibold">{{ $item->hs_code }}</td>
                        <td class="fw-semibold text-dark">{{ $item->product_description }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                        <td class="text-center text-secondary small">{{ $item->uom }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->value_excl_tax, 2) }}</td>
                        <td class="text-center">{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '-' }}</td>
                        <td class="text-end fw-bold" style="background-color: #FEF9C3; color: #0F172A;">
                            {{ number_format($item->value_excl_tax - $item->discount_amount, 2) }}
                        </td>
                    </tr>
                    <!-- Row 2: Sub-row for Detailed Tax Breakdown -->
                    <tr style="font-size: 10.5px; background-color: #F8FAFC; border-bottom: 2px solid #CBD5E1;">
                        <td colspan="9" class="py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-secondary">
                                <div><span class="text-muted">Sale Type:</span> <strong class="text-dark">{{ $item->sale_type ?: 'Standard' }}</strong></div>
                                <div><span class="text-muted">SRO #:</span> <strong class="text-dark">{{ $item->sro_schedule_no ?: 'N/A' }}</strong></div>
                                <div>
                                    <span class="text-muted">Taxable Value:</span>
                                    <strong class="text-dark">
                                        @if (($item->fixed_notified_value ?? $item->retail_price) > 0)
                                            MSRP {{ number_format($item->fixed_notified_value ?? $item->retail_price, 2) }} &times; {{ (int)$item->quantity }} = {{ number_format(($item->fixed_notified_value ?? $item->retail_price) * $item->quantity, 2) }}
                                        @else
                                            {{ number_format($item->value_excl_tax, 2) }}
                                        @endif
                                    </strong>
                                </div>
                                <div><span class="text-muted">GST:</span> <strong class="text-dark">{{ (float)$item->tax_rate }}% &nbsp; {{ number_format($item->sales_tax_amount, 2) }}</strong></div>
                                <div><span class="text-muted">F.Tax:</span> <strong class="text-dark">{{ (float)$item->further_tax_rate }}% &nbsp; {{ number_format($item->further_tax_amount, 2) }}</strong></div>
                                <div><span class="text-muted">FED:</span> <strong class="text-dark">{{ number_format($item->fed_payable_amount ?? 0, 2) }}</strong></div>
                                <div><span class="text-muted">Extra:</span> <strong class="text-dark">{{ number_format($item->extra_tax_amount ?? 0, 2) }}</strong></div>
                                <div class="px-2 py-1 rounded" style="background-color: #DCFCE7; color: #15803D; font-weight: 700;">
                                    Total Tax: {{ number_format($item->sales_tax_amount + $item->further_tax_amount + ($item->extra_tax_amount ?? 0) + ($item->fed_payable_amount ?? 0), 2) }}
                                </div>
                                <div class="px-2 py-1 rounded" style="background-color: #DBEAFE; color: #1E40AF; font-weight: 800;">
                                    Total + Tax: {{ number_format($item->total_value_incl_tax, 2) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Bottom Section: FBR QR Verification Container & Summary Totals -->
        <div class="d-flex justify-content-between align-items-start mt-3 mb-4">
            <!-- Left Side: FBR QR Code Container -->
            <div>
                @if (!empty($invoice->fbr_invoice_number))
                    <div class="fbr-qr-box">
                        <div class="d-flex align-items-center mb-2" style="color: #16A34A; font-weight: 800; font-size: 11px;">
                            <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>FBR VERIFIED INVOICE</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            @php
                                $qrPayload = $invoice->fbr_qr_code ?: ('https://e.fbr.gov.pk/es/VerifyInvoice.aspx?fbr_inv_no=' . urlencode($invoice->fbr_invoice_number));
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode($qrPayload);
                            @endphp
                            <img src="{{ $qrUrl }}" alt="FBR QR Code" width="110" height="110" style="border: 1px solid #CBD5E1; padding: 2px; border-radius: 4px;">
                            <div class="ms-2 text-center" style="border: 1px solid #3B82F6; border-radius: 6px; padding: 8px 4px; background: #EFF6FF; width: 90px;">
                                <div style="font-weight: 900; font-size: 14px; color: #1E40AF; line-height: 1;">FBR</div>
                                <div style="font-weight: 800; font-size: 9.5px; color: #2563EB; letter-spacing: 0.5px;">DIGITAL</div>
                                <div style="font-size: 7px; color: #475569; text-transform: uppercase;">INVOICING SYSTEM</div>
                            </div>
                        </div>
                        <div class="text-center">
                            <div style="font-size: 9.5px; color: #64748B;">Scan to verify</div>
                            <div style="font-size: 9px; font-weight: 800; font-family: monospace; color: #16A34A; word-break: break-all;">
                                {{ $invoice->fbr_invoice_number }}
                            </div>
                        </div>
                    </div>
                @else
                    <div style="border: 2px dashed #94A3B8; border-radius: 8px; padding: 14px; background: #F8FAFC; width: 235px; text-align: center;">
                        <div class="text-muted fw-bold small mb-1"><i class="bi bi-clock-history me-1"></i> FBR Invoicing Pending</div>
                        <div style="font-size: 10px; color: #64748B;">Official QR code and FBR Invoice Number will be generated when submitted to FBR.</div>
                    </div>
                @endif
            </div>

            <!-- Right Side: Summary Totals Card -->
            <div style="width: 360px;">
                <table class="table table-sm table-borderless mb-2" style="font-size: 12.5px;">
                    <tbody>
                        <tr style="border-bottom: 1px solid #E2E8F0;">
                            <td class="text-secondary py-1">Total Value (Excl. Tax)</td>
                            <td class="text-end fw-semibold text-dark py-1">{{ number_format($invoice->sales_value_excl_tax, 2) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #E2E8F0;">
                            <td class="text-secondary py-1">Total GST</td>
                            <td class="text-end fw-semibold text-dark py-1">{{ number_format($invoice->sales_tax_amount, 2) }}</td>
                        </tr>
                        @if ($invoice->further_tax_amount > 0)
                            <tr style="border-bottom: 1px solid #E2E8F0;">
                                <td class="text-secondary py-1">Total Further Tax</td>
                                <td class="text-end fw-semibold text-dark py-1">{{ number_format($invoice->further_tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if (($invoice->extra_tax_amount ?? 0) > 0)
                            <tr style="border-bottom: 1px solid #E2E8F0;">
                                <td class="text-secondary py-1">Total Extra Tax</td>
                                <td class="text-end fw-semibold text-dark py-1">{{ number_format($invoice->extra_tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if (($invoice->fed_amount ?? 0) > 0)
                            <tr style="border-bottom: 1px solid #E2E8F0;">
                                <td class="text-secondary py-1">Total FED</td>
                                <td class="text-end fw-semibold text-dark py-1">{{ number_format($invoice->fed_amount, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Solid Blue Grand Total Bar -->
                <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded mb-2" style="background-color: #0F3B82; color: #FFFFFF;">
                    <span style="font-size: 13.5px; font-weight: 700;">Grand Total</span>
                    <span style="font-size: 15.5px; font-weight: 800;">Rs {{ number_format($invoice->total_invoice_value, 2) }}</span>
                </div>

                <!-- Soft Red Amount Due Bar -->
                <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded" style="background-color: #FEE2E2; color: #DC2626;">
                    <span style="font-size: 13px; font-weight: 700;">Amount Due</span>
                    <span style="font-size: 14.5px; font-weight: 800;">Rs {{ number_format($invoice->net_receivable_amount ?: $invoice->total_invoice_value, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-4 text-muted" style="font-size: 10px;">
            <div>{{ $business->name }}</div>
            <div class="text-end">
                Generated: {{ now()->format('d/m/Y, H:i:s') }}<br>
                This is a computer-generated document
            </div>
        </div>
    </div>

</body>
</html>
