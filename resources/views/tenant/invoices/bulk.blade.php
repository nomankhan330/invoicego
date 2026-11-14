@extends('layouts.tenant')

@section('title', 'Excel Bulk Invoicing')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Excel Bulk Invoicing</h1>
        <div class="text-muted small">
            Upload 10, 20 or more invoices at once using an Excel/CSV spreadsheet &middot; Fast validation and FBR fiscalization
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tenant.invoices.sample-template') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Download Excel / CSV Template</span>
        </a>
        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-ig-outline">
            <i class="bi bi-arrow-left"></i> Back to Invoices
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Upload Form -->
    <div class="col-lg-6">
        <div class="ig-content-card h-100">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark fs-6 mb-0">Upload Bulk Spreadsheet</h5>
                    <div class="text-muted small">Supports .csv exported from Microsoft Excel, Google Sheets, or LibreOffice</div>
                </div>
            </div>

            <form method="POST" action="{{ route('tenant.invoices.process-bulk-upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark">Select Spreadsheet File <span class="text-danger">*</span></label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    <div class="form-text small">Maximum file size: 5 MB. Must match the standard template columns.</div>
                </div>

                <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 border">
                    <input class="form-check-input ms-0 me-3" type="checkbox" name="submit_to_fbr" id="submitToFbrToggle" value="1">
                    <label class="form-check-label fw-semibold text-dark small" for="submitToFbrToggle">
                        Automatically Submit Valid Invoices to FBR
                        <div class="text-muted fw-normal" style="font-size: 11.5px;">If unchecked, all invoices will be safely saved as drafts for review.</div>
                    </label>
                </div>

                <button type="submit" class="btn btn-ig-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-upload"></i>
                    <span>Process & Import Invoices</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Step-by-Step Instructions & Column Reference -->
    <div class="col-lg-6">
        <div class="ig-content-card h-100">
            <h5 class="fw-bold text-dark fs-6 mb-3">How Bulk Invoicing Works</h5>
            <ol class="text-muted small ps-3 mb-4" style="line-height: 1.8;">
                <li><strong class="text-dark">Download:</strong> Click <a href="{{ route('tenant.invoices.sample-template') }}" class="text-primary fw-semibold">Download Template</a> to get the pre-formatted CSV template.</li>
                <li><strong class="text-dark">Fill Data:</strong> Enter your invoice rows. You can group multiple product lines under the same invoice by using the same <code class="text-primary">Invoice Reference</code>.</li>
                <li><strong class="text-dark">Upload & Validate:</strong> Select the saved file and click process. The system validates tax rates, buyer ATL status, and HS codes automatically.</li>
            </ol>

            <h6 class="fw-bold text-dark small mb-2">Required Template Columns:</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered small text-muted mb-0" style="font-size: 11.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Column Name</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Invoice Reference</td><td>Unique invoice identifier</td><td>INV-2026-001</td></tr>
                        <tr><td>Invoice Date</td><td>Date in current PST month</td><td>{{ now('Asia/Karachi')->format('Y-m-d') }}</td></tr>
                        <tr><td>Buyer Name</td><td>Buyer / Customer Name</td><td>Ali Khan</td></tr>
                        <tr><td>Buyer NTN or CNIC</td><td>7-digit NTN or 13-digit CNIC</td><td>7654321</td></tr>
                        <tr><td>Buyer Type</td><td>registered / unregistered</td><td>registered</td></tr>
                        <tr><td>HS Code</td><td>FBR 8-digit tariff code</td><td>0101.2100</td></tr>
                        <tr><td>Quantity & Price</td><td>Unit price & quantity</td><td>Qty: 2, Price: 150000</td></tr>
                        <tr><td>Tax Rate</td><td>Sales Tax Percentage</td><td>18%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
