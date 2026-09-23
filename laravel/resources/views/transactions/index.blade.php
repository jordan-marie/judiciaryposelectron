@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Transaction Log & Query Engine</h3>
        <p class="text-muted small mb-0">Search, filter, and inspect dynamic weighbridge transactions stored in SQLite</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('transactions.export-csv', request()->query()) }}" class="btn btn-outline-success fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
    </div>
</div>

<!-- Flexible Query & Filter Panel -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-body py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-uppercase d-flex align-items-center gap-2">
            <i class="bi bi-funnel-fill text-primary"></i> Filter Transactions
        </h6>
        @if(request()->anyFilled(['form_id', 'status', 'plate_number', 'date_from', 'date_to', 'search', 'meta_field', 'meta_value']))
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-link text-decoration-none text-danger fw-semibold">
                <i class="bi bi-x-circle me-1"></i> Clear Filters
            </a>
        @endif
    </div>
    <div class="card-body p-3">
        <form action="{{ route('transactions.index') }}" method="GET">
            <div class="row g-3">
                <!-- Search Keyword -->
                <div class="col-12 col-md-3">
                    <label for="search" class="form-label small fw-bold">Search (Code / Plate / Values)</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="e.g. WB-2026 or ABC-1234">
                </div>

                <!-- Form Filter Dropdown -->
                <div class="col-12 col-md-3">
                    <label for="form_id" class="form-label small fw-bold">Form Type</label>
                    <select class="form-select form-select-sm" id="form_id" name="form_id">
                        <option value="">All Dynamic Forms</option>
                        @foreach($forms as $f)
                            <option value="{{ $f->id }}" {{ request('form_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-12 col-md-2">
                    <label for="status" class="form-label small fw-bold">Status</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-12 col-md-2">
                    <label for="date_from" class="form-label small fw-bold">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_to" class="form-label small fw-bold">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>

                <!-- Grouped Select2 Dynamic Custom Field Filter Dropdown -->
                <div class="col-12 col-md-5">
                    <label for="meta_field" class="form-label small fw-bold">Select Dynamic Field (by Form Category)</label>
                    <select class="form-select form-select-sm select2-meta-field" id="meta_field" name="meta_field">
                        <option value="">-- All Dynamic Fields --</option>
                        @foreach($forms as $formCategory)
                            <optgroup label="Form Category: {{ $formCategory->name }}">
                                @foreach($formCategory->fields as $field)
                                    <option value="{{ $field->field_name }}" {{ request('meta_field') == $field->field_name ? 'selected' : '' }}>
                                        {{ $field->label }} [{{ $field->field_name }}]
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label for="meta_value" class="form-label small fw-bold">Dynamic Field Value Query</label>
                    <input type="text" class="form-control form-control-sm" id="meta_value" name="meta_value" value="{{ request('meta_value') }}" placeholder="e.g. Apex or Municipal Solid Waste">
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions DataTable Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="transactions-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Form Name</th>
                        <th>Plate No.</th>
                        <th>Gross (KG)</th>
                        <th>Tare (KG)</th>
                        <th>Net Weight (KG)</th>
                        <th>Status</th>
                        <th>Operator</th>
                        <th>Timestamp</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace">
                                <a href="{{ route('transactions.show', $tx->id) }}" class="text-decoration-none">
                                    {{ $tx->transaction_code }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $tx->form ? $tx->form->name : 'N/A' }}</span>
                            </td>
                            <td class="fw-bold text-uppercase">{{ $tx->plate_number ?? 'N/A' }}</td>
                            <td>{{ number_format($tx->gross_weight, 2) }}</td>
                            <td>{{ number_format($tx->tare_weight, 2) }}</td>
                            <td class="fw-bold text-primary">{{ number_format($tx->net_weight, 2) }}</td>
                            <td>
                                @if($tx->status === 'completed')
                                    <span class="badge bg-success-subtle text-success">Completed</span>
                                @elseif($tx->status === 'in_progress')
                                    <span class="badge bg-warning-subtle text-warning">In Progress</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="small">{{ $tx->creator ? $tx->creator->name : 'N/A' }}</td>
                            <td class="small text-muted">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transactions.show', $tx->id) }}" class="btn btn-outline-primary" title="View Ticket Details">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                    <a href="{{ route('transactions.print', $tx->id) }}" target="_blank" class="btn btn-outline-secondary" title="Print Ticket">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">No transactions found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer bg-body py-3 border-top">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for Dynamic Meta Field Filter
        $('.select2-meta-field').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Select Dynamic Field --',
            allowClear: true,
            width: '100%'
        });

        // DataTables initialization
        $('#transactions-table').DataTable({
            paging: false,
            info: false,
            searching: false,
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
