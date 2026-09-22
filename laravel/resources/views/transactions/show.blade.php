@extends('layouts.app')

@section('title', 'Weighbridge Ticket - ' . $transaction->transaction_code)
@section('header-title', 'Weighment Ticket')

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #ticket-print-area, #ticket-print-area * {
            visibility: visible;
        }
        #ticket-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        #sidebar, .navbar-top, .footer, .no-print {
            display: none !important;
        }
    }

    .ticket-card {
        border: 2px solid var(--bs-border-color);
        border-radius: 12px;
        background: var(--bs-body-bg);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Transactions
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary fw-bold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Ticket
            </button>
            <a href="{{ route('transactions.create') }}" class="btn btn-success fw-bold">
                <i class="bi bi-plus-lg me-1"></i> New Weighment
            </a>
        </div>
    </div>

    <!-- Ticket View Container -->
    <div class="row justify-content-center">
        <div class="col-lg-8" id="ticket-print-area">
            <div class="card ticket-card p-4 shadow-sm">
                <!-- Ticket Header -->
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-truck text-primary fs-2"></i>
                            <h3 class="fw-bold mb-0">WEIGHSYS LOGISTICS</h3>
                        </div>
                        <small class="text-muted d-block">Official Industrial Weighbridge Ticket</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-5 font-monospace mb-1">{{ $transaction->transaction_code }}</span>
                        <div class="fs-7 text-muted">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>

                <!-- Primary Vehicle & Weight Summary Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-body-tertiary h-100">
                            <div class="text-muted fs-7 fw-semibold text-uppercase mb-1">Vehicle Plate Number</div>
                            <div class="badge bg-dark text-warning fs-4 font-monospace px-3 py-2 border border-warning">{{ $transaction->plate_number }}</div>
                            <div class="mt-2 fs-7 text-muted">Status: <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }} text-capitalize">{{ $transaction->status }}</span></div>
                            <div class="fs-7 text-muted">Operator: <strong>{{ $transaction->user ? $transaction->user->name : 'N/A' }}</strong></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-body-tertiary h-100">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Gross Weight:</span>
                                <span class="fw-bold fs-6">{{ number_format($transaction->gross_weight, 2) }} kg</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Tare Weight:</span>
                                <span class="fw-bold fs-6">{{ number_format($transaction->tare_weight, 2) }} kg</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="fw-bold text-primary fs-5">NET WEIGHT:</span>
                                <span class="fw-bold text-success fs-4">{{ number_format($transaction->net_weight, 2) }} kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Dynamic Form Fields Meta -->
                @if($transaction->meta->count() > 0)
                <h6 class="fw-bold text-uppercase text-muted border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-1"></i> Transaction Custom Metadata</h6>
                <div class="row g-3 mb-4">
                    @foreach($transaction->meta as $meta)
                        <div class="col-md-6">
                            <div class="p-2 border rounded">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">{{ str_replace('_', ' ', $meta->field_name) }}</small>
                                <div class="fw-bold fs-6">{{ $meta->field_value ?: '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Signature Lines for Physical Print -->
                <div class="row text-center pt-4 mt-4 border-top">
                    <div class="col-6">
                        <div style="height: 50px;"></div>
                        <div class="border-top pt-1 text-muted fs-7">Driver Signature</div>
                    </div>
                    <div class="col-6">
                        <div style="height: 50px;"></div>
                        <div class="border-top pt-1 text-muted fs-7">Weighbridge Operator Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
