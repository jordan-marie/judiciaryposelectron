@extends('layouts.app')

@section('title', 'Dashboard - Weighbridge Management System')
@section('header-title', 'System Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Key Weighbridge Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                        <i class="bi bi-truck fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold text-uppercase">Today's Weighments</div>
                        <h3 class="fw-bold mb-0">{{ number_format($todayCount) }}</h3>
                        <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> {{ $completedToday }} Completed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                        <i class="bi bi-hourglass-split fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold text-uppercase">In-Progress / Pending</div>
                        <h3 class="fw-bold mb-0">{{ number_format($inProgressCount) }}</h3>
                        <small class="text-muted">Awaiting Tare Weighment</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                        <i class="bi bi-speedometer fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold text-uppercase">Net Tonnage Today</div>
                        <h3 class="fw-bold mb-0">{{ number_format($totalNetWeightToday / 1000, 2) }} <span class="fs-6 text-muted fw-normal">Tons</span></h3>
                        <small class="text-muted">{{ number_format($totalNetWeightToday, 2) }} KG Total Net</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                        <i class="bi bi-ui-checks fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold text-uppercase">Active Scale Forms</div>
                        <h3 class="fw-bold mb-0">{{ $activeFormsCount }}</h3>
                        <small class="text-muted">Dynamic Builder Active</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Transactions Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Weighments</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">View All Log</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Plate Number</th>
                                    <th>Gross Weight</th>
                                    <th>Tare Weight</th>
                                    <th>Net Weight</th>
                                    <th>Status</th>
                                    <th>Operator</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                <tr>
                                    <td>
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="fw-bold text-decoration-none">
                                            {{ $tx->transaction_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-warning font-monospace fs-7 px-2 py-1">{{ $tx->plate_number ?: 'N/A' }}</span>
                                    </td>
                                    <td>{{ number_format($tx->gross_weight, 2) }} kg</td>
                                    <td>{{ number_format($tx->tare_weight, 2) }} kg</td>
                                    <td class="fw-bold text-success">{{ number_format($tx->net_weight, 2) }} kg</td>
                                    <td>
                                        @if($tx->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($tx->status === 'in_progress')
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        @else
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $tx->user ? $tx->user->name : 'System' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No transactions recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hardware & System Health Sidebar Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body-tertiary">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-cpu me-2 text-primary"></i> Hardware Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-camera-video text-success fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold">LPR Camera Feed</div>
                                <small class="text-muted">IP Camera - Lane 1</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">ONLINE</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-display text-primary fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold">Digital Scale Indicator</div>
                                <small class="text-muted">RS-232 COM1 (9600 8N1)</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">CONNECTED</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-database text-info fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold">SQLite Engine</div>
                                <small class="text-muted">Local Database Storage</small>
                            </div>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">ACTIVE</span>
                    </div>
                </div>
            </div>

            <!-- Operator Quick Action Shortcuts -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-warning"></i> Quick Shortcuts</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    @can('create-transactions')
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg fw-bold d-flex align-items-center justify-content-center gap-2 py-3">
                        <i class="bi bi-plus-circle-fill"></i> Launch Scale Operator Interface
                    </a>
                    @endcan

                    @can('manage-forms')
                    <a href="{{ route('form-builder.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-sliders"></i> Dynamic Form Builder
                    </a>
                    @endcan

                    <button class="btn btn-outline-dark d-flex align-items-center justify-content-center gap-2" id="dashFullscreenBtn">
                        <i class="bi bi-arrows-fullscreen"></i> Fullscreen Operator View (Alt+F)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dashFullscreenBtn').on('click', function() {
            $('#fullscreenToggleBtn').click();
        });
    });
</script>
@endpush
