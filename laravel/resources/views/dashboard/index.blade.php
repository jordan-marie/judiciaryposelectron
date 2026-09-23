@extends('layouts.app')

@section('title', 'Dashboard - WeighSys Terminal')
@section('header-title', 'System Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Key Weighbridge Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-4 bg-primary bg-opacity-10 p-3 text-primary me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-truck fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase tracking-wider">Today's Weighments</div>
                        <h2 class="fw-extrabold mb-0 tracking-tight">{{ number_format($todayCount) }}</h2>
                        <small class="text-success fw-bold fs-8"><i class="bi bi-check-circle-fill me-1"></i> {{ $completedToday }} Completed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-4 bg-warning bg-opacity-10 p-3 text-warning me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-hourglass-split fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase tracking-wider">In-Progress / Pending</div>
                        <h2 class="fw-extrabold mb-0 tracking-tight">{{ number_format($inProgressCount) }}</h2>
                        <small class="text-muted fs-8">Awaiting Tare Weighment</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-4 bg-success bg-opacity-10 p-3 text-success me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-speedometer2 fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase tracking-wider">Net Tonnage Today</div>
                        <h2 class="fw-extrabold mb-0 tracking-tight">{{ number_format($totalNetWeightToday / 1000, 2) }} <span class="fs-6 text-muted fw-semibold">Tons</span></h2>
                        <small class="text-muted fs-8">{{ number_format($totalNetWeightToday, 2) }} KG Total Net</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-4 bg-info bg-opacity-10 p-3 text-info me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-ui-checks-grid fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase tracking-wider">Active Scale Forms</div>
                        <h2 class="fw-extrabold mb-0 tracking-tight">{{ $activeFormsCount }}</h2>
                        <small class="text-muted fs-8">Dynamic Builder Engine</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Transactions Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i> Recent Weighments Log
                    </h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary fw-bold">View All Log</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-8 text-uppercase fw-bold text-muted">
                                <tr>
                                    <th class="ps-4">Code</th>
                                    <th>Plate Number</th>
                                    <th>Gross Weight</th>
                                    <th>Tare Weight</th>
                                    <th>Net Weight</th>
                                    <th>Status</th>
                                    <th class="pe-4">Operator</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('transactions.show', $tx->id) }}" class="fw-extrabold font-monospace text-decoration-none">
                                            {{ $tx->transaction_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-warning font-monospace fs-7 px-2.5 py-1 border border-warning">{{ $tx->plate_number ?: 'N/A' }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($tx->gross_weight, 2) }} kg</td>
                                    <td class="fw-semibold">{{ number_format($tx->tare_weight, 2) }} kg</td>
                                    <td class="fw-bold text-success fs-6">{{ number_format($tx->net_weight, 2) }} kg</td>
                                    <td>
                                        @if($tx->status === 'completed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Completed</span>
                                        @elseif($tx->status === 'in_progress')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5">In Progress</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        <span class="text-muted fs-7 fw-semibold">{{ $tx->user ? $tx->user->name : 'System' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No transactions recorded yet.</td>
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
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-cpu-fill text-primary"></i> Hardware Infrastructure
                    </h5>
                </div>
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-success bg-opacity-10 p-2.5 text-success me-3">
                                <i class="bi bi-camera-video-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-7">LPR Camera Feed</div>
                                <small class="text-muted fs-8">IP Camera - Lane 1</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">ONLINE</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-2.5 text-primary me-3">
                                <i class="bi bi-display-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-7">Scale Indicator</div>
                                <small class="text-muted fs-8">RS-232 COM1 (9600 8N1)</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">CONNECTED</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 bg-info bg-opacity-10 p-2.5 text-info me-3">
                                <i class="bi bi-database-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-7">SQLite Database Engine</div>
                                <small class="text-muted fs-8">Local Embedded Storage</small>
                            </div>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">ACTIVE</span>
                    </div>
                </div>
            </div>

            <!-- Operator Quick Action Shortcuts -->
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Operator Quick Shortcuts
                    </h5>
                </div>
                <div class="card-body p-3.5 d-grid gap-2.5">
                    @can('create-transactions')
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg fw-bold d-flex align-items-center justify-content-center gap-2 py-3 shadow-sm">
                        <i class="bi bi-play-circle-fill fs-5"></i> Launch Scale Operator Terminal
                    </a>
                    @endcan

                    @can('manage-forms')
                    <a href="{{ route('form-builder.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 fw-semibold">
                        <i class="bi bi-sliders"></i> Form Builder Canvas
                    </a>
                    @endcan

                    <button class="btn btn-outline-dark d-flex align-items-center justify-content-center gap-2 fw-semibold" id="dashFullscreenBtn">
                        <i class="bi bi-arrows-fullscreen"></i> Fullscreen View Mode (Alt+F)
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
