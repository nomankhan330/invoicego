@extends('layouts.tenant')

@section('title', 'Sandbox Scenarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">FBR Sandbox Test Scenarios (SN001 – SN028)</h1>
        <div class="text-muted small">
            PRAL DI API v1.12 compliance tester &middot; Autofill, edit JSON payloads (Postman-style), and verify against Iris Sandbox
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success py-2 px-3 fw-semibold">
            <i class="bi bi-shield-check me-1"></i> {{ count($progress) }} / {{ count($scenarios) }} Completed
        </span>
    </div>
</div>

<div class="row g-3">
    @forelse ($scenarios as $s)
        @php
            $isPassed = isset($progress[$s->id]) && $progress[$s->id]->pivot->status === 'passed';
            $passedAt = $isPassed ? $progress[$s->id]->pivot->passed_at : null;

            // Generate realistic scenario payload for Postman runner
            $sampleJson = json_encode([
                'invoiceType' => str_contains(strtolower($s->title), 'credit') ? 'Credit Note' : (str_contains(strtolower($s->title), 'debit') ? 'Debit Note' : 'Sale Invoice'),
                'invoiceDate' => now('Asia/Karachi')->format('Y-m-d'),
                'sellerNTN' => $business->ntn_cnic ?? '1234125',
                'sellerBusinessName' => $business->name ?? 'OC1',
                'sellerProvince' => $business->province ?? 'Punjab',
                'buyerNTN' => str_contains(strtolower($s->title), 'unregistered') ? '3520112345678' : '7654321',
                'buyerName' => str_contains(strtolower($s->title), 'unregistered') ? 'Tariq Retail Client' : 'Apex Logistics Pvt Ltd',
                'buyerType' => str_contains(strtolower($s->title), 'unregistered') ? 'unregistered' : 'registered',
                'scenario' => $s->scenario_code,
                'items' => [
                    [
                        'hsCode' => '0101.2100',
                        'productDescription' => $s->title,
                        'rate' => str_contains(strtolower($s->title), 'reduced') ? 10 : (str_contains(strtolower($s->title), 'exempt') ? 0 : 18),
                        'quantity' => 1,
                        'unitPrice' => 5000.00,
                        'salesTax' => str_contains(strtolower($s->title), 'exempt') ? 0 : 900.00,
                        'furtherTax' => str_contains(strtolower($s->title), 'further') ? 200.00 : 0.00,
                    ]
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="ig-content-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $isPassed ? 'bg-success' : 'bg-primary' }} bg-opacity-10 {{ $isPassed ? 'text-success' : 'text-primary' }} fw-bold font-monospace fs-6">
                            {{ $s->scenario_code }}
                        </span>
                        @if ($isPassed)
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> Passed
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                Pending Test
                            </span>
                        @endif
                    </div>

                    <h5 class="fw-bold text-dark fs-6 mb-2">{{ $s->title }}</h5>
                    <p class="text-muted small mb-3" style="line-height: 1.5; font-size: 12.5px;">
                        {{ $s->description }}
                    </p>
                </div>

                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small" style="font-size: 11px;">
                        @if ($isPassed)
                            Passed: {{ \Carbon\Carbon::parse($passedAt)->format('d M, H:i') }}
                        @else
                            Not executed yet
                        @endif
                    </span>

                    <div class="d-flex gap-1">
                        <!-- Postman-style JSON Editor Modal Trigger -->
                        <button type="button" class="btn btn-sm btn-outline-secondary open-scenario-modal-btn"
                            data-code="{{ $s->scenario_code }}"
                            data-title="{{ $s->title }}"
                            data-payload="{{ htmlspecialchars($sampleJson, ENT_QUOTES, 'UTF-8') }}"
                            title="Open in Postman-style Runner">
                            <i class="bi bi-code-slash"></i> JSON
                        </button>

                        <form method="POST" action="{{ route('tenant.sandbox-scenarios.run', $s->scenario_code) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ig-outline d-flex align-items-center gap-1">
                                <i class="bi bi-play-circle"></i> Run
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            No scenarios available.
        </div>
    @endforelse
</div>

<!-- ================= POSTMAN-STYLE SCENARIO RUNNER MODAL ================= -->
<div class="modal fade" id="scenarioEditorModal" tabindex="-1" aria-labelledby="scenarioEditorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title font-display fw-bold text-dark fs-5" id="modalScenarioTitle">Execute Scenario</h5>
                    <div class="text-muted small" id="modalScenarioSubtitle">Endpoint: POST https://gw.fbr.gov.pk/im/v1/sandbox</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scenarioRunForm" method="POST" action="">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold small text-dark mb-0">Sandbox Request Payload (Autofilled & Editable):</label>
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">JSON &middot; PRAL v1.12</span>
                    </div>
                    <textarea name="payload" id="scenarioPayloadInput" class="form-control font-monospace" rows="14" style="font-size: 12.5px; background-color: #1E293B; color: #38BDF8; line-height: 1.5; border-radius: 8px;"></textarea>
                    <div class="form-text small mt-2">
                        You can modify any tax rate, HS code, or buyer details before dispatching directly to the FBR Sandbox API.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ig-primary btn-sm px-4 d-flex align-items-center gap-2">
                        <i class="bi bi-send-fill"></i>
                        <span>Execute Against FBR Sandbox</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('scenarioEditorModal'));
    const modalTitle = document.getElementById('modalScenarioTitle');
    const modalSubtitle = document.getElementById('modalScenarioSubtitle');
    const payloadInput = document.getElementById('scenarioPayloadInput');
    const form = document.getElementById('scenarioRunForm');

    document.querySelectorAll('.open-scenario-modal-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            const title = this.getAttribute('data-title');
            const payload = this.getAttribute('data-payload');

            modalTitle.textContent = `${code}: ${title}`;
            modalSubtitle.textContent = `Target: FBR PRAL Sandbox Gateway &middot; Scenario ${code}`;
            payloadInput.value = payload;
            form.action = `/sandbox-scenarios/${code}/run`;

            modal.show();
        });
    });
});
</script>
@endpush
@endsection
