<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} — Official FBR Print</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0F172A;
            background: #F1F5F9;
            padding: 30px 0;
            font-size: 13px;
        }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background: #FFFFFF;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            box-sizing: border-box;
        }
        .fbr-header-badge {
            background-color: #047857;
            color: #FFFFFF;
            padding: 6px 16px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 4px;
            display: inline-block;
        }
        .table-invoice th {
            background-color: #F8FAFC !important;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            border-top: 1px solid #CBD5E1;
            border-bottom: 2px solid #94A3B8;
        }
        .watermark-sandbox {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 56px;
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
                padding: 15mm 15mm !important;
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
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="background-color: #5850EC; border-color: #5850EC;">
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

        <!-- Document Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold mb-1 font-display" style="color: #1E293B;">{{ $business->name }}</h3>
                <div class="text-secondary small mb-1">{{ $business->address }}</div>
                <div class="text-secondary small">
                    <strong>NTN/CNIC:</strong> {{ $business->ntn_cnic }} &middot;
                    <strong>STRN:</strong> {{ $business->strn ?? 'Unregistered' }} &middot;
                    <strong>Province:</strong> {{ $business->province }}
                </div>
            </div>

            <div class="text-end">
                <div class="fbr-header-badge mb-2">
                    FBR DIGITAL INVOICING
                </div>
                <h4 class="fw-bold text-dark mb-1">SALES TAX INVOICE</h4>
                <div class="small text-muted"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                <div class="small text-muted"><strong>Date:</strong> {{ $invoice->invoice_date->format('d-M-Y') }}</div>
            </div>
        </div>

        <!-- FBR Official Verification Box -->
        <div class="card bg-light border-0 rounded-3 p-3 mb-4">
            <div class="row align-items-center">
                <div class="col-8">
                    <div class="text-uppercase fw-bold text-success small mb-1" style="font-size: 11px;">
                        <i class="bi bi-shield-check me-1"></i> Official FBR Fiscal Verification Details
                    </div>
                    <div class="mb-1">
                        <strong>FBR Invoice Number:</strong>
                        <span class="font-monospace text-primary fw-bold">{{ $invoice->fbr_invoice_number ?? 'PENDING SUBMISSION' }}</span>
                    </div>
                    <div class="text-muted small">
                        <strong>DI Specification:</strong> PRAL DI API v1.12 &middot;
                        <strong>Submission Mode:</strong> {{ strtoupper($invoice->environment) }} &middot;
                        <strong>Verified At:</strong> {{ $invoice->fbr_submitted_at?->format('d-M-Y H:i:s') ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-4 text-end">
                    @php
                        $qrPayload = $invoice->fbr_qr_code ?? ('https://fbr.gov.pk/verify?inv=' . $invoice->invoice_number . '&fbr=' . ($invoice->fbr_invoice_number ?? 'DEMO'));
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrPayload);
                    @endphp
                    <img src="{{ $qrUrl }}" alt="FBR Verification QR Code" width="85" height="85" class="border p-1 bg-white rounded">
                    <div class="text-muted" style="font-size: 10px; margin-top: 3px;">Scan to verify with FBR</div>
                </div>
            </div>
        </div>

        <!-- Buyer Information Box -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="border rounded-2 p-3 bg-white">
                    <div class="text-uppercase text-secondary fw-bold small mb-2" style="font-size: 10.5px;">Buyer / Customer Details</div>
                    <div class="row">
                        <div class="col-6">
                            <div class="fw-bold text-dark fs-6">{{ $invoice->customer->name }}</div>
                            <div class="text-secondary small">{{ $invoice->customer->address ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="small">
                                <strong>{{ $invoice->customer->isRegistered() ? 'NTN (Registered):' : 'CNIC (Unregistered):' }}</strong>
                                {{ $invoice->customer->ntn_cnic }}
                            </div>
                            @if ($invoice->customer->strn)
                                <div class="small"><strong>STRN:</strong> {{ $invoice->customer->strn }}</div>
                            @endif
                            <div class="small"><strong>Province:</strong> {{ $invoice->customer->province ?? 'Punjab' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered table-invoice align-middle mb-4">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Item Description</th>
                    <th style="width: 15%;">HS / PCT Code</th>
                    <th style="width: 8%;" class="text-end">Qty</th>
                    <th style="width: 7%;">UOM</th>
                    <th style="width: 15%;" class="text-end">Unit Price</th>
                    <th style="width: 15%;" class="text-end">Excl. Tax (Rs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $idx => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                        <td class="fw-semibold text-dark">{{ $item->product_description }}</td>
                        <td class="text-muted"><code>{{ $item->hs_code }}</code></td>
                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->uom }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item->value_excl_tax, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Totals Table -->
        <div class="row justify-content-end mb-4">
            <div class="col-6">
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr>
                            <td class="text-secondary">Total Value (Excl. Tax):</td>
                            <td class="text-end fw-semibold">Rs {{ number_format($invoice->sales_value_excl_tax, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Sales Tax (GST @ 18%):</td>
                            <td class="text-end fw-semibold">Rs {{ number_format($invoice->sales_tax_amount, 2) }}</td>
                        </tr>
                        @if ($invoice->further_tax_amount > 0)
                            <tr>
                                <td class="text-secondary">Further Tax (4% §3(1A)):</td>
                                <td class="text-end fw-semibold">Rs {{ number_format($invoice->further_tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->st_withheld_amount > 0)
                            <tr>
                                <td class="text-secondary">ST Withheld at Source:</td>
                                <td class="text-end fw-semibold">Rs {{ number_format($invoice->st_withheld_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td class="fw-bold fs-6">Gross Invoice Value:</td>
                            <td class="text-end fw-bold fs-6 text-dark">Rs {{ number_format($invoice->total_invoice_value, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Sign-off -->
        <div class="border-top pt-4 mt-5 text-muted" style="font-size: 11px;">
            <div class="row">
                <div class="col-8">
                    <p class="mb-1">This is a computer-generated tax invoice issued through InvoiceGO in compliance with FBR Sales Tax Rules 2006 &amp; PRAL Digital Invoicing API v1.12.</p>
                    <p class="mb-0">Powered by <strong>InvoiceGO Multi-Tenant SaaS Platform</strong>.</p>
                </div>
                <div class="col-4 text-center">
                    <div style="border-bottom: 1px solid #CBD5E1; height: 35px; margin-bottom: 6px;"></div>
                    <div class="text-secondary">Authorized Signature &amp; Stamp</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
