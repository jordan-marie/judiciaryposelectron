@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Weighment Ticket Details: <span class="font-monospace text-primary">{{ $transaction->transaction_code }}</span></h3>
        <p class="text-muted small mb-0">Logged on {{ $transaction->created_at->format('M d, Y \a\t H:i:s') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Transactions
        </a>
        <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Print Ticket
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Primary Ticket Information -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-ticket-perforated text-primary me-2"></i>Weighbridge Ticket Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold">Transaction Code</td>
                            <td class="fw-bold font-monospace text-primary fs-5">{{ $transaction->transaction_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Form Used</td>
                            <td><span class="badge bg-secondary-subtle text-secondary fs-6">{{ $transaction->form ? $transaction->form->name : 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Vehicle Plate Number</td>
                            <td class="fw-bold text-uppercase fs-5">{{ $transaction->plate_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Status</td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transaction->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Operator</td>
                            <td>{{ $transaction->creator ? $transaction->creator->name : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weight Breakdown Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-calculator text-success me-2"></i>Weight Measurements</h5>
            </div>
            <div class="card-body p-4 text-center">
                <div class="row g-3">
                    <div class="col-4 border-end">
                        <div class="text-uppercase text-muted small fw-bold">Gross Weight</div>
                        <h4 class="fw-bold text-success mt-1">{{ number_format($transaction->gross_weight, 2) }}</h4>
                        <span class="small text-muted">KG</span>
                    </div>
                    <div class="col-4 border-end">
                        <div class="text-uppercase text-muted small fw-bold">Tare Weight</div>
                        <h4 class="fw-bold text-warning mt-1">{{ number_format($transaction->tare_weight, 2) }}</h4>
                        <span class="small text-muted">KG</span>
                    </div>
                    <div class="col-4">
                        <div class="text-uppercase text-muted small fw-bold">Net Weight</div>
                        <h4 class="fw-bold text-primary mt-1">{{ number_format($transaction->net_weight, 2) }}</h4>
                        <span class="small text-muted">KG</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Metadata Key-Value Card -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-input-cursor-text text-info me-2"></i>Dynamic Custom Field Values</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Field Name</th>
                            <th class="pe-4">Captured Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaction->meta as $m)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ Str::headline($m->field_name) }}</td>
                                <td class="pe-4 fw-bold">
                                    @if($m->field_value === '1')
                                        <span class="badge bg-success-subtle text-success">Yes / Checked</span>
                                    @elseif($m->field_value === '0')
                                        <span class="badge bg-secondary-subtle text-secondary">No / Unchecked</span>
                                    @else
                                        {{ $m->field_value }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No dynamic custom metadata captured for this transaction.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
