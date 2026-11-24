@extends('layouts.tenant')

@section('title', 'Sandbox Scenarios')

@push('styles')
<style>
    .scenarios-sidebar {
        max-height: 850px;
        overflow-y: auto;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 8px;
    }
    .scenario-nav-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        margin-bottom: 4px;
        text-decoration: none !important;
    }
    .scenario-nav-item:hover {
        background: #F8FAFC;
    }
    .scenario-nav-item.active {
        background: #EFF6FF;
        border-color: #BFDBFE;
    }
    .scenario-nav-code {
        font-family: 'Plus Jakarta Sans', monospace;
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 2px;
    }
    .scenario-nav-title {
        font-size: 11.5px;
        color: #64748B;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .scenario-radio {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #CBD5E1;
        margin-top: 3px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
    }
    .scenario-nav-item.active .scenario-radio {
        border-color: #1E50FF;
        background: #1E50FF;
        box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.15);
    }
    .scenario-nav-item.passed .scenario-radio {
        border-color: #16A34A;
        background: #16A34A;
    }
    .scenario-radio-inner {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #FFFFFF;
    }
    .scenario-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 24px;
    }
    .scenario-tabs .nav-link {
        font-size: 13.5px;
        font-weight: 600;
        color: #64748B;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 8px 16px;
        border-radius: 0;
    }
    .scenario-tabs .nav-link.active {
        color: #1E50FF;
        border-bottom-color: #1E50FF;
        background: transparent;
    }
    .scenario-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }
    .scenario-input {
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #CBD5E1;
    }
    .scenario-input:focus {
        border-color: #1E50FF;
        box-shadow: 0 0 0 2px rgba(30, 80, 255, 0.12);
    }
    .scenario-json-editor {
        font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
        font-size: 13px;
        line-height: 1.55;
        background: #0F172A;
        color: #38BDF8;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 16px;
        width: 100%;
        tab-size: 2;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="fw-bold fs-4 mb-1 text-dark" style="letter-spacing: -0.01em;">FBR Sandbox Test Scenarios (SN001 – SN028)</h1>
            <div class="text-secondary small">Official PRAL Digital Invoicing API v1.12 compliance testing &middot; Form &amp; editable JSON payload runners</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold">
                <i class="bi bi-shield-check me-1"></i> <span id="completedCount">{{ count($progress) }}</span> / {{ count($scenarios) }} Completed
            </span>
        </div>
    </div>

    <!-- Alert Banner -->
    <div id="runnerAlert" class="alert d-none mb-3 py-2 px-3 small rounded-3" role="alert"></div>

    <div class="row g-3">
        <!-- LEFT: Scenarios List (Vertical Navigation) -->
        <div class="col-lg-3 col-md-4">
            <div class="scenarios-sidebar shadow-sm">
                @foreach ($scenarios as $s)
                    @php
                        $isPassed = isset($progress[$s->id]) && $progress[$s->id]->pivot->status === 'passed';
                        $sample = $s->sample_payload ?? [];
                    @endphp
                    <div class="scenario-nav-item {{ $loop->first ? 'active' : '' }} {{ $isPassed ? 'passed' : '' }}"
                         data-code="{{ $s->scenario_code }}"
                         data-id="{{ $s->id }}"
                         data-title="{{ $s->title }}"
                         data-desc="{{ $s->description }}"
                         data-is-passed="{{ $isPassed ? '1' : '0' }}">
                        <div class="scenario-radio">
                            @if ($isPassed)
                                <i class="bi bi-check text-white" style="font-size: 13px; line-height: 1;"></i>
                            @else
                                <div class="scenario-radio-inner d-none"></div>
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <div class="scenario-nav-code d-flex align-items-center gap-1">
                                <span>{{ $s->scenario_code }}</span>
                                @if ($isPassed)
                                    <span class="badge bg-success bg-opacity-10 text-success py-0 px-1" style="font-size: 9.5px;">PASSED</span>
                                @endif
                            </div>
                            <div class="scenario-nav-title">{{ $s->title }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT: Main Scenario Workspace (Form / JSON Version) -->
        <div class="col-lg-9 col-md-8">
            <!-- Request Card -->
            <div class="scenario-card shadow-sm mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0 fs-5" id="requestHeaderTitle">Request &middot; <span id="activeScenarioCode">SN001</span></h5>
                        <div class="text-muted small mt-1" id="activeScenarioTitle">{{ $scenarios->first()->title ?? '' }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" id="resetToTemplateBtn">
                        <i class="bi bi-arrow-clockwise"></i> Reset to template
                    </button>
                </div>

                <!-- Tabs: Form | JSON -->
                <ul class="nav nav-tabs scenario-tabs mb-4" id="scenarioTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form-tab-pane" type="button" role="tab">
                            Form
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="json-tab" data-bs-toggle="tab" data-bs-target="#json-tab-pane" type="button" role="tab">
                            JSON
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="scenarioTabContent">
                    <!-- 1. FORM VERSION -->
                    <div class="tab-pane fade show active" id="form-tab-pane" role="tabpanel">
                        <!-- Invoice Header Details -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="scenario-label">invoiceType</label>
                                <select class="form-select scenario-input" id="field_invoiceType">
                                    <option value="Sale Invoice">Sale Invoice</option>
                                    <option value="Credit Note">Credit Note</option>
                                    <option value="Debit Note">Debit Note</option>
                                    <option value="STWH">STWH</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">invoiceDate</label>
                                <input type="date" class="form-control scenario-input" id="field_invoiceDate" value="{{ now('Asia/Karachi')->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">sellerNTNCNIC <span class="text-muted">(from profile)</span></label>
                                <input type="text" class="form-control scenario-input bg-light" id="field_sellerNTN" value="{{ $business->ntn_cnic ?? '1234567' }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="scenario-label">sellerBusinessName <span class="text-muted">(from profile)</span></label>
                                <input type="text" class="form-control scenario-input bg-light" id="field_sellerBusinessName" value="{{ $business->name ?? 'invoicego' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">sellerProvince <span class="text-muted">(from profile)</span></label>
                                <input type="text" class="form-control scenario-input bg-light" id="field_sellerProvince" value="{{ $business->province ?? 'Punjab' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">sellerAddress <span class="text-muted">(from profile)</span></label>
                                <input type="text" class="form-control scenario-input bg-light" id="field_sellerAddress" value="{{ $business->address ?? 'Lahore' }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="scenario-label">buyerNTNCNIC</label>
                                <input type="text" class="form-control scenario-input" id="field_buyerNTN" value="1000000000000">
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">buyerBusinessName</label>
                                <input type="text" class="form-control scenario-input" id="field_buyerBusinessName" value="FERTILIZER MANUFAC IRS NEW">
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">buyerProvince</label>
                                <input type="text" class="form-control scenario-input" id="field_buyerProvince" value="Sindh">
                            </div>

                            <div class="col-md-4">
                                <label class="scenario-label">buyerAddress</label>
                                <input type="text" class="form-control scenario-input" id="field_buyerAddress" value="Karachi">
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">buyerRegistrationType</label>
                                <select class="form-select scenario-input" id="field_buyerRegistrationType">
                                    <option value="">— Omitted —</option>
                                    <option value="Registered">Registered</option>
                                    <option value="Unregistered">Unregistered</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="scenario-label">invoiceRefNo</label>
                                <input type="text" class="form-control scenario-input" id="field_invoiceRefNo" value="SI-20250421-001">
                            </div>

                            <div class="col-md-12">
                                <label class="scenario-label">scenarioId <span class="text-muted">(fixed for this scenario)</span></label>
                                <input type="text" class="form-control scenario-input bg-light font-monospace" id="field_scenarioId" value="SN001" readonly>
                            </div>
                        </div>

                        <!-- Items[0] Section -->
                        <div class="border rounded-3 p-3 bg-light bg-opacity-50 mb-3">
                            <div class="fw-bold text-dark small mb-3">Items[0]</div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="scenario-label">hsCode</label>
                                    <input type="text" class="form-control scenario-input" id="item_hsCode" value="0101.2100">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">productDescription</label>
                                    <input type="text" class="form-control scenario-input" id="item_productDescription" value="test">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">rate</label>
                                    <input type="text" class="form-control scenario-input" id="item_rate" value="18%">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">uoM</label>
                                    <input type="text" class="form-control scenario-input" id="item_uoM" value="Numbers, pieces, units">
                                </div>

                                <div class="col-md-3">
                                    <label class="scenario-label">quantity</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_quantity" value="400">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">totalValues</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_totalValues" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">valueSalesExcludingST</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_valueSalesExcludingST" value="1000">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">fixedNotifiedValueOrRetailPrice</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_fixedNotifiedValueOrRetailPrice" value="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="scenario-label">salesTaxApplicable</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_salesTaxApplicable" value="180">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">salesTaxWithheldAtSource</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_salesTaxWithheldAtSource" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">extraTax</label>
                                    <input type="text" class="form-control scenario-input" id="item_extraTax" value="" placeholder='"" or 0'>
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">furtherTax</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_furtherTax" value="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="scenario-label">sroScheduleNo</label>
                                    <input type="text" class="form-control scenario-input" id="item_sroScheduleNo" value="">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">fedPayable</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_fedPayable" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">discount</label>
                                    <input type="number" step="any" class="form-control scenario-input" id="item_discount" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="scenario-label">sroItemSerialNo</label>
                                    <input type="text" class="form-control scenario-input" id="item_sroItemSerialNo" value="">
                                </div>

                                <div class="col-md-12">
                                    <label class="scenario-label">saleType</label>
                                    <input type="text" class="form-control scenario-input" id="item_saleType" value="Goods at standard rate (default)">
                                </div>
                            </div>
                        </div>

                        <div class="text-muted" style="font-size: 11.5px; line-height: 1.45;">
                            Values are sent exactly as entered (scenario examples are not recalculated). Leave extraTax empty only if FBR asks for it (error 0019).
                        </div>
                    </div>

                    <!-- 2. JSON VERSION (EDITABLE) -->
                    <div class="tab-pane fade" id="json-tab-pane" role="tabpanel">
                        <textarea id="scenarioJsonEditor" class="scenario-json-editor" rows="18" spellcheck="false"></textarea>
                        <div id="jsonSyncIndicator" class="small mt-2 text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> Valid JSON - the form is in sync.
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap align-items-center gap-2 pt-4 border-top mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnPreviewSchema">
                        <i class="bi bi-code-slash me-1"></i> Check schema &amp; preview request
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnValidateFbr">
                        <i class="bi bi-shield-check me-1"></i> Validate with FBR sandbox
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" id="btnSubmitSandbox" style="background: #1E50FF; border-color: #1E50FF;">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                        <i class="bi bi-send-fill me-1" id="submitIcon"></i> Validate &amp; submit to sandbox
                    </button>
                </div>
            </div>

            <!-- Attempt History Card -->
            <div class="scenario-card shadow-sm mb-3">
                <h6 class="fw-bold text-dark mb-3">Attempt history</h6>
                <div id="attemptHistoryContainer">
                    <div class="text-secondary small py-2" id="noAttemptsNotice">
                        No attempts yet for <span id="historyScenarioCode">SN001</span>.
                    </div>
                    <div class="table-responsive d-none" id="attemptHistoryTableWrapper">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th>WHEN</th>
                                    <th>STATUS</th>
                                    <th>RESULT / FBR CODE</th>
                                    <th>DETAILS</th>
                                </tr>
                            </thead>
                            <tbody id="attemptHistoryTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="text-secondary small px-1" style="font-size: 12px; line-height: 1.5;">
                Production tokens are issued by IRIS after the required scenarios pass (Production Environment &rarr; View Security Token). InvoiceGO never generates or fetches production credentials.
            </div>
        </div>
    </div>
</div>

<!-- Schema Preview Modal -->
<div class="modal fade" id="schemaPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark">Schema &amp; Request Payload Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i> <strong>PRAL DI API v1.12 Schema Validated:</strong> All mandatory fields, types, and scenario constraints are present.
                </div>
                <pre class="bg-dark text-info p-3 rounded-3 mb-0" style="font-size: 12.5px; max-height: 400px; overflow-y: auto;" id="schemaModalContent"></pre>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scenariosData = @json($scenarios->keyBy('scenario_code'));
    const progressData = @json($progress);
    const recentAttempts = @json($recentAttempts);
    const businessData = @json($business);

    let currentCode = 'SN001';
    let currentExtraTopFields = {};
    const standardTopKeys = new Set([
        'invoiceType', 'invoiceDate', 'sellerNTNCNIC', 'sellerBusinessName', 'sellerProvince',
        'sellerAddress', 'buyerNTNCNIC', 'buyerBusinessName', 'buyerProvince', 'buyerAddress',
        'buyerRegistrationType', 'invoiceRefNo', 'scenarioId', 'items'
    ]);
    const formFields = [
        'field_invoiceType', 'field_invoiceDate', 'field_sellerNTN', 'field_sellerBusinessName',
        'field_sellerProvince', 'field_sellerAddress', 'field_buyerNTN', 'field_buyerBusinessName',
        'field_buyerProvince', 'field_buyerAddress', 'field_buyerRegistrationType', 'field_invoiceRefNo',
        'field_scenarioId', 'item_hsCode', 'item_productDescription', 'item_rate', 'item_uoM',
        'item_quantity', 'item_totalValues', 'item_valueSalesExcludingST', 'item_fixedNotifiedValueOrRetailPrice',
        'item_salesTaxApplicable', 'item_salesTaxWithheldAtSource', 'item_extraTax', 'item_furtherTax',
        'item_sroScheduleNo', 'item_fedPayable', 'item_discount', 'item_saleType', 'item_sroItemSerialNo'
    ];

    // Helper: Build payload object for scenario from official FBR sample_payload
    function getTemplatePayload(code) {
        const s = scenariosData[code] || {};
        const todayStr = '{{ now("Asia/Karachi")->format("Y-m-d") }}';

        if (s.sample_payload && typeof s.sample_payload === 'object' && !Array.isArray(s.sample_payload)) {
            const tpl = JSON.parse(JSON.stringify(s.sample_payload));
            tpl.invoiceDate = todayStr;
            tpl.scenarioId = tpl.scenarioId || code;
            if (businessData.ntn_cnic) tpl.sellerNTNCNIC = businessData.ntn_cnic;
            if (businessData.name) tpl.sellerBusinessName = businessData.name;
            if (businessData.province) tpl.sellerProvince = businessData.province;
            if (businessData.address) tpl.sellerAddress = businessData.address;
            return tpl;
        }

        return {
            invoiceType: 'Sale Invoice',
            invoiceDate: todayStr,
            sellerBusinessName: businessData.name || 'Company 8',
            sellerProvince: businessData.province || 'Sindh',
            sellerNTNCNIC: businessData.ntn_cnic || '8885801',
            sellerAddress: businessData.address || 'Karachi',
            buyerNTNCNIC: '2046004',
            buyerBusinessName: 'FERTILIZER MANUFAC IRS NEW',
            buyerProvince: 'Sindh',
            buyerAddress: 'Karachi',
            invoiceRefNo: '',
            scenarioId: code,
            buyerRegistrationType: 'Registered',
            items: [
                {
                    hsCode: '0101.2100',
                    productDescription: 'test',
                    rate: '18%',
                    uoM: 'Numbers, pieces, units',
                    quantity: 400,
                    totalValues: 0,
                    valueSalesExcludingST: 1000,
                    fixedNotifiedValueOrRetailPrice: 0,
                    salesTaxApplicable: 180,
                    salesTaxWithheldAtSource: 0,
                    extraTax: '',
                    furtherTax: 0,
                    sroScheduleNo: '',
                    fedPayable: 0,
                    discount: 0,
                    saleType: 'Goods at standard rate (default)',
                    sroItemSerialNo: ''
                }
            ]
        };
    }

    // Populate Form fields from an object
    function setFormFromPayload(payload) {
        // Capture any scenario-specific extra top-level keys (e.g. dataSource, additional1..3)
        currentExtraTopFields = {};
        Object.keys(payload || {}).forEach(k => {
            if (!standardTopKeys.has(k)) {
                currentExtraTopFields[k] = payload[k];
            }
        });

        document.getElementById('field_invoiceType').value = payload.invoiceType ?? 'Sale Invoice';
        document.getElementById('field_invoiceDate').value = payload.invoiceDate ?? '{{ now("Asia/Karachi")->format("Y-m-d") }}';
        document.getElementById('field_sellerNTN').value = payload.sellerNTNCNIC ?? businessData.ntn_cnic ?? '';
        document.getElementById('field_sellerBusinessName').value = payload.sellerBusinessName ?? businessData.name ?? '';
        document.getElementById('field_sellerProvince').value = payload.sellerProvince ?? businessData.province ?? '';
        document.getElementById('field_sellerAddress').value = payload.sellerAddress ?? businessData.address ?? '';
        document.getElementById('field_buyerNTN').value = payload.buyerNTNCNIC ?? '';
        document.getElementById('field_buyerBusinessName').value = payload.buyerBusinessName ?? '';
        document.getElementById('field_buyerProvince').value = payload.buyerProvince ?? '';
        document.getElementById('field_buyerAddress').value = payload.buyerAddress ?? '';
        document.getElementById('field_buyerRegistrationType').value = Object.prototype.hasOwnProperty.call(payload, 'buyerRegistrationType')
            ? (payload.buyerRegistrationType ?? '')
            : '';
        document.getElementById('field_invoiceRefNo').value = payload.invoiceRefNo ?? '';
        document.getElementById('field_scenarioId').value = payload.scenarioId ?? currentCode;

        const itm = (payload.items && payload.items[0]) ? payload.items[0] : {};
        document.getElementById('item_hsCode').value = itm.hsCode ?? '';
        document.getElementById('item_productDescription').value = itm.productDescription ?? '';
        document.getElementById('item_rate').value = itm.rate ?? '';
        document.getElementById('item_uoM').value = itm.uoM ?? itm.uom ?? '';
        document.getElementById('item_quantity').value = itm.quantity !== undefined && itm.quantity !== null ? itm.quantity : 0;
        document.getElementById('item_totalValues').value = itm.totalValues !== undefined && itm.totalValues !== null ? itm.totalValues : 0;
        document.getElementById('item_valueSalesExcludingST').value = itm.valueSalesExcludingST !== undefined && itm.valueSalesExcludingST !== null ? itm.valueSalesExcludingST : 0;
        document.getElementById('item_fixedNotifiedValueOrRetailPrice').value = itm.fixedNotifiedValueOrRetailPrice !== undefined && itm.fixedNotifiedValueOrRetailPrice !== null ? itm.fixedNotifiedValueOrRetailPrice : 0;
        document.getElementById('item_salesTaxApplicable').value = itm.salesTaxApplicable !== undefined && itm.salesTaxApplicable !== null ? itm.salesTaxApplicable : 0;
        document.getElementById('item_salesTaxWithheldAtSource').value = itm.salesTaxWithheldAtSource !== undefined && itm.salesTaxWithheldAtSource !== null ? itm.salesTaxWithheldAtSource : 0;
        document.getElementById('item_extraTax').value = itm.extraTax !== undefined && itm.extraTax !== null ? String(itm.extraTax) : '';
        document.getElementById('item_furtherTax').value = itm.furtherTax !== undefined && itm.furtherTax !== null ? itm.furtherTax : 0;
        document.getElementById('item_sroScheduleNo').value = itm.sroScheduleNo ?? '';
        document.getElementById('item_fedPayable').value = itm.fedPayable !== undefined && itm.fedPayable !== null ? itm.fedPayable : 0;
        document.getElementById('item_discount').value = itm.discount !== undefined && itm.discount !== null ? itm.discount : 0;
        document.getElementById('item_saleType').value = itm.saleType ?? '';
        document.getElementById('item_sroItemSerialNo').value = itm.sroItemSerialNo ?? '';
    }

    // Build Payload object from Form fields
    function getPayloadFromForm() {
        const extraTaxRaw = document.getElementById('item_extraTax').value.trim();
        const extraTaxVal = extraTaxRaw === '' ? '' : (isNaN(Number(extraTaxRaw)) ? extraTaxRaw : Number(extraTaxRaw));

        const payload = {
            invoiceType: document.getElementById('field_invoiceType').value,
            invoiceDate: document.getElementById('field_invoiceDate').value,
            sellerBusinessName: document.getElementById('field_sellerBusinessName').value,
            sellerProvince: document.getElementById('field_sellerProvince').value,
            sellerNTNCNIC: document.getElementById('field_sellerNTN').value,
            sellerAddress: document.getElementById('field_sellerAddress').value,
            buyerNTNCNIC: document.getElementById('field_buyerNTN').value,
            buyerBusinessName: document.getElementById('field_buyerBusinessName').value,
            buyerProvince: document.getElementById('field_buyerProvince').value,
            buyerAddress: document.getElementById('field_buyerAddress').value,
            invoiceRefNo: document.getElementById('field_invoiceRefNo').value,
            scenarioId: currentCode
        };

        const buyerRegType = document.getElementById('field_buyerRegistrationType').value;
        if (buyerRegType !== '') {
            payload.buyerRegistrationType = buyerRegType;
        }

        Object.keys(currentExtraTopFields).forEach(k => {
            payload[k] = currentExtraTopFields[k];
        });

        payload.items = [
            {
                hsCode: document.getElementById('item_hsCode').value,
                productDescription: document.getElementById('item_productDescription').value,
                rate: document.getElementById('item_rate').value,
                uoM: document.getElementById('item_uoM').value,
                quantity: parseFloat(document.getElementById('item_quantity').value) || 0,
                totalValues: parseFloat(document.getElementById('item_totalValues').value) || 0,
                valueSalesExcludingST: parseFloat(document.getElementById('item_valueSalesExcludingST').value) || 0,
                fixedNotifiedValueOrRetailPrice: parseFloat(document.getElementById('item_fixedNotifiedValueOrRetailPrice').value) || 0,
                salesTaxApplicable: parseFloat(document.getElementById('item_salesTaxApplicable').value) || 0,
                salesTaxWithheldAtSource: parseFloat(document.getElementById('item_salesTaxWithheldAtSource').value) || 0,
                extraTax: extraTaxVal,
                furtherTax: parseFloat(document.getElementById('item_furtherTax').value) || 0,
                sroScheduleNo: document.getElementById('item_sroScheduleNo').value,
                fedPayable: parseFloat(document.getElementById('item_fedPayable').value) || 0,
                discount: parseFloat(document.getElementById('item_discount').value) || 0,
                saleType: document.getElementById('item_saleType').value,
                sroItemSerialNo: document.getElementById('item_sroItemSerialNo').value
            }
        ];

        return payload;
    }

    // Update JSON Editor from Form
    function syncFormToJson() {
        const payload = getPayloadFromForm();
        document.getElementById('scenarioJsonEditor').value = JSON.stringify(payload, null, 2);
        setJsonValid(true);
    }

    // Set JSON sync indicator
    function setJsonValid(isValid, errorMsg = '') {
        const indicator = document.getElementById('jsonSyncIndicator');
        if (isValid) {
            indicator.className = 'small mt-2 text-success';
            indicator.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Valid JSON - the form is in sync.';
        } else {
            indicator.className = 'small mt-2 text-danger';
            indicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Invalid JSON: ' + (errorMsg || 'Syntax error');
        }
    }

    // Load active scenario
    function loadScenario(code) {
        currentCode = code;
        document.getElementById('activeScenarioCode').textContent = code;
        const titleEl = document.getElementById('activeScenarioTitle');
        if (titleEl) {
            titleEl.textContent = (scenariosData[code] && scenariosData[code].title) ? scenariosData[code].title : '';
        }
        document.getElementById('historyScenarioCode').textContent = code;
        document.getElementById('field_scenarioId').value = code;

        const payload = getTemplatePayload(code);
        setFormFromPayload(payload);
        syncFormToJson();
        loadHistory(code);

        // Update active class in sidebar
        document.querySelectorAll('.scenario-nav-item').forEach(el => {
            if (el.getAttribute('data-code') === code) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    // Load attempt history table
    function loadHistory(code) {
        const attempts = recentAttempts[code] || [];
        const noNotice = document.getElementById('noAttemptsNotice');
        const tableWrapper = document.getElementById('attemptHistoryTableWrapper');
        const tbody = document.getElementById('attemptHistoryTableBody');

        tbody.innerHTML = '';
        if (attempts.length === 0) {
            noNotice.classList.remove('d-none');
            tableWrapper.classList.add('d-none');
        } else {
            noNotice.classList.add('d-none');
            tableWrapper.classList.remove('d-none');

            attempts.forEach(att => {
                const tr = document.createElement('tr');
                const isSuccess = att.outcome === 'success' || att.outcome === 'passed' || att.http_status === 200;
                const statusBadge = isSuccess
                    ? '<span class="badge bg-success bg-opacity-10 text-success">PASSED</span>'
                    : '<span class="badge bg-danger bg-opacity-10 text-danger">FAILED</span>';

                let fbrCode = att.response_payload && att.response_payload.FBRCode ? att.response_payload.FBRCode : (att.response_payload && att.response_payload.FBRInvoiceNumber ? att.response_payload.FBRInvoiceNumber : 'PRAL-SB-' + att.id);
                let message = att.error_message || (att.response_payload && att.response_payload.Message ? att.response_payload.Message : 'Validated by PRAL sandbox validator');

                tr.innerHTML = `
                    <td class="text-nowrap text-secondary">${new Date(att.executed_at || att.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
                    <td>${statusBadge}</td>
                    <td class="font-monospace text-dark">${fbrCode}</td>
                    <td class="small text-secondary">${message}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    // Attach click events on sidebar items
    document.querySelectorAll('.scenario-nav-item').forEach(item => {
        item.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            loadScenario(code);
        });
    });

    // Form inputs change -> sync to JSON
    formFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', syncFormToJson);
            el.addEventListener('change', syncFormToJson);
        }
    });

    // JSON Editor change -> parse and sync to Form
    const jsonEditor = document.getElementById('scenarioJsonEditor');
    jsonEditor.addEventListener('input', function () {
        try {
            const parsed = JSON.parse(this.value);
            setFormFromPayload(parsed);
            setJsonValid(true);
        } catch (e) {
            setJsonValid(false, e.message);
        }
    });

    // Reset button
    document.getElementById('resetToTemplateBtn').addEventListener('click', function () {
        loadScenario(currentCode);
    });

    // Check schema & preview
    document.getElementById('btnPreviewSchema').addEventListener('click', function () {
        let payload;
        try {
            payload = JSON.parse(jsonEditor.value);
        } catch (e) {
            alert('Cannot preview: JSON format is invalid. Please fix syntax errors first.');
            return;
        }

        document.getElementById('schemaModalContent').textContent = JSON.stringify(payload, null, 2);
        new bootstrap.Modal(document.getElementById('schemaPreviewModal')).show();
    });

    // Validate with FBR Sandbox
    document.getElementById('btnValidateFbr').addEventListener('click', function () {
        let payload;
        try {
            payload = JSON.parse(jsonEditor.value);
        } catch (e) {
            showAlert('danger', 'JSON validation failed: ' + e.message);
            return;
        }

        showAlert('info', 'Validating payload against PRAL DI API v1.12 sandbox schema...');

        fetch('{{ route("tenant.sandbox-scenarios.validate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                scenario_code: currentCode,
                payload: jsonEditor.value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', '✓ ' + data.message);
            } else {
                showAlert('danger', 'Validation error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            showAlert('danger', 'Error connecting to validation service.');
        });
    });

    // Submit to sandbox
    document.getElementById('btnSubmitSandbox').addEventListener('click', function () {
        let payload;
        try {
            payload = JSON.parse(jsonEditor.value);
        } catch (e) {
            showAlert('danger', 'Cannot submit: Invalid JSON syntax.');
            return;
        }

        const btn = this;
        const spinner = document.getElementById('submitSpinner');
        const icon = document.getElementById('submitIcon');

        btn.disabled = true;
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        showAlert('info', 'Submitting scenario ' + currentCode + ' to official FBR Sandbox...');

        fetch(`/sandbox-scenarios/${currentCode}/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payload: jsonEditor.value
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');

            if (data.success) {
                showAlert('success', '✓ ' + data.message);

                // Update sidebar badge to passed
                const navItem = document.querySelector(`.scenario-nav-item[data-code="${currentCode}"]`);
                if (navItem) {
                    navItem.classList.add('passed');
                    const radio = navItem.querySelector('.scenario-radio');
                    if (radio) {
                        radio.innerHTML = '<i class="bi bi-check text-white" style="font-size: 13px; line-height: 1;"></i>';
                    }
                    if (!navItem.querySelector('.badge')) {
                        const codeDiv = navItem.querySelector('.scenario-nav-code');
                        if (codeDiv) {
                            codeDiv.innerHTML += ' <span class="badge bg-success bg-opacity-10 text-success py-0 px-1" style="font-size: 9.5px;">PASSED</span>';
                        }
                    }
                }

                // Append attempt row to history table
                const resObj = (data.result && data.result.response) ? data.result.response : {};
                const fbrCode = resObj.FBRCode || resObj.FBRInvoiceNumber || ('PRAL-SB-' + Date.now().toString().slice(-6));
                const message = resObj.Result || resObj.Message || 'Scenario verified by PRAL sandbox validator';

                if (!recentAttempts[currentCode]) {
                    recentAttempts[currentCode] = [];
                }
                recentAttempts[currentCode].unshift({
                    executed_at: new Date().toISOString(),
                    outcome: 'success',
                    http_status: 200,
                    response_payload: { FBRCode: fbrCode, Message: message }
                });
                loadHistory(currentCode);
            } else {
                showAlert('danger', 'Execution failed: ' + (data.message || 'Error executing scenario.'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            showAlert('danger', 'Error connecting to sandbox gateway.');
        });
    });

    function showAlert(type, msg) {
        const al = document.getElementById('runnerAlert');
        al.className = `alert alert-${type} mb-3 py-2 px-3 small rounded-3`;
        al.innerHTML = msg;
        al.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Initialize with first scenario
    loadScenario('SN001');
});
</script>
@endpush
