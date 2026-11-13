@extends('layouts.tenant')

@section('title', 'Sandbox Scenarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">FBR Sandbox Test Scenarios</h1>
        <div class="text-muted small">
            PRAL DI API v1.12 compliance requirement &middot; Run scenarios to qualify your sandbox token before production issuance
        </div>
    </div>
</div>

<div class="row g-3">
    @forelse ($scenarios as $s)
        @php
            $isPassed = isset($progress[$s->id]) && $progress[$s->id]->pivot->status === 'passed';
            $passedAt = $isPassed ? $progress[$s->id]->pivot->passed_at : null;
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
                    <p class="text-muted small mb-3" style="line-height: 1.5;">
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

                    <form method="POST" action="{{ route('tenant.sandbox-scenarios.run', $s->scenario_code) }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ig-outline d-flex align-items-center gap-1">
                            <i class="bi bi-play-circle"></i> Run Test
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            No scenarios available.
        </div>
    @endforelse
</div>
@endsection
