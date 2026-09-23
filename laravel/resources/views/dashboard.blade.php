@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Operational Dashboard</h3>
        <p class="text-muted small mb-0">Real-time summary of scale console transactions and system health</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('scale.index') }}" class="btn btn-primary fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-aspect-ratio-fill"></i> Launch Scale Terminal
        </a>
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Today Weighments</span>
                    <h3 class="fw-bold mb-0 mt-1">{{ $todayCount }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-truck fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">In Progress</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $inProgressCount }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Net Tonnage Today</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($todayTonnage, 2) }} <span class="fs-6 text-muted fw-normal">Tons</span></h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-speedometer fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Active Dynamic Forms</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info">{{ $activeFormsCount }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                    <i class="bi bi-journal-text fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Scale Terminal Quick Action -->
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Recent Weighments</h5>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Form Type</th>
                            <th>Plate No.</th>
                            <th>Net Weight</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $tx)
                            <tr>
                                <td class="fw-bold font-monospace">{{ $tx->transaction_code }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $tx->form ? $tx->form->name : 'N/A' }}</span></td>
                                <td class="fw-semibold">{{ $tx->plate_number ?? 'N/A' }}</td>
                                <td><strong>{{ number_format($tx->net_weight, 0) }}</strong> KG</td>
                                <td>
                                    @if($tx->status === 'completed')
                                        <span class="badge bg-success-subtle text-success">Completed</span>
                                    @elseif($tx->status === 'in_progress')
                                        <span class="badge bg-warning-subtle text-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $tx->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No transactions recorded today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <!-- Scale Operator Console Quick Launch Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="bi bi-aspect-ratio fs-1"></i>
                    <div>
                        <h5 class="fw-bold mb-0">Scale Terminal</h5>
                        <small class="opacity-75">Active operator mode</small>
                    </div>
                </div>
                <p class="small opacity-90 mb-3">
                    Switch between active forms assigned to your role, capture live weights, read LPR plate numbers, and submit weight entries.
                </p>
                <a href="{{ route('scale.index') }}" class="btn btn-light w-100 fw-bold text-primary">
                    Open Scale Console <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- System Status Summary Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Hardware Connection Status</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-body-tertiary mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 text-success fs-5"></i>
                        <div>
                            <div class="fw-semibold small">Weighbridge Indicator</div>
                            <div class="text-muted extra-small" style="font-size: 0.75rem;">COM3 - 9600 8N1</div>
                        </div>
                    </div>
                    <span class="badge bg-success">STABLE</span>
                </div>

                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-body-tertiary">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-camera-video text-info fs-5"></i>
                        <div>
                            <div class="fw-semibold small">LPR Camera #1</div>
                            <div class="text-muted extra-small" style="font-size: 0.75rem;">RTSP://192.168.1.100</div>
                        </div>
                    </div>
                    <span class="badge bg-info text-dark">READY</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
