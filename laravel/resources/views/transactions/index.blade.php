@extends('layouts.app')

@section('title', 'Transactions Log - Weighbridge System')
@section('header-title', 'Weighment Transactions & Query Engine')

@section('content')
<div class="container-fluid">
    <!-- Dynamic Query & Search Panel -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-funnel-fill me-2 text-primary"></i> SQLite Dynamic Query Engine</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">JSON / EAV Query Engine</span>
        </div>
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET" id="queryFilterForm">
                <div class="row g-3">
                    <!-- Global Search -->
                    <div class="col-md-3">
                        <label for="search" class="form-label fs-7 fw-semibold">Global Keyword Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Code, Plate, or Any Field">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label for="status" class="form-label fs-7 fw-semibold">Transaction Status</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label for="date_from" class="form-label fs-7 fw-semibold">Date From</label>
                        <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label for="date_to" class="form-label fs-7 fw-semibold">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <!-- Dynamic Custom Field Filter Dropdown -->
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Dynamic Custom Field Query</label>
                        <div class="input-group input-group-sm">
                            <select class="form-select" id="meta_field" name="meta_field" style="max-width: 45%;">
                                <option value="">Select Field</option>
                                @foreach($dynamicFields as $df)
                                    <option value="{{ $df->field_name }}" {{ request('meta_field') === $df->field_name ? 'selected' : '' }}>
                                        {{ $df->label }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" id="meta_value" name="meta_value" value="{{ request('meta_value') }}" placeholder="Value search...">
                        </div>
                    </div>
                </div>

                <!-- Query Action Controls -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <span class="text-muted fs-7">Matching Weighments: <strong>{{ $transactions->total() }}</strong> records</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                        <button type="submit" class="btn btn-sm btn-primary fw-bold">
                            <i class="bi bi-filter me-1"></i> Apply Filter
                        </button>
                        @can('export-transactions')
                        <a href="{{ route('transactions.exportCsv', request()->query()) }}" class="btn btn-sm btn-success fw-bold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                        </a>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Datatable Results Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="transactionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Date & Time</th>
                            <th>Plate Number</th>
                            <th>Gross (kg)</th>
                            <th>Tare (kg)</th>
                            <th>Net Weight (kg)</th>
                            <th>Dynamic Details</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <a href="{{ route('transactions.show', $tx->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $tx->transaction_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fs-7 fw-semibold">{{ $tx->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted fs-7">{{ $tx->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark text-warning font-monospace fs-6 px-2 py-1">{{ $tx->plate_number }}</span>
                            </td>
                            <td>{{ number_format($tx->gross_weight, 2) }}</td>
                            <td>{{ number_format($tx->tare_weight, 2) }}</td>
                            <td class="fw-bold text-success fs-6">{{ number_format($tx->net_weight, 2) }}</td>
                            <td>
                                @foreach($tx->meta->take(2) as $m)
                                    <div class="fs-7 text-truncate" style="max-width: 180px;">
                                        <small class="text-muted fw-bold">{{ str_replace('_', ' ', $m->field_name) }}:</small> {{ $m->field_value }}
                                    </div>
                                @endforeach
                                @if($tx->meta->count() > 2)
                                    <small class="text-primary fst-italic">+{{ $tx->meta->count() - 2 }} more fields</small>
                                @endif
                            </td>
                            <td>
                                @if($tx->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($tx->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary" title="View Ticket">
                                        <i class="bi bi-ticket-detailed"></i>
                                    </a>
                                    @can('manage-forms')
                                    <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete transaction record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No weighbridge transactions match the search query.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ $transactions->firstItem() ?: 0 }} to {{ $transactions->lastItem() ?: 0 }} of {{ $transactions->total() }} entries</small>
                <div>
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables.net for table enhancement
        $('#transactionsTable').DataTable({
            paging: false, // Handled by Laravel pagination
            info: false,
            searching: false, // Handled by SQLite dynamic query panel
            ordering: true,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [6, 8] }
            ]
        });
    });
</script>
@endpush
