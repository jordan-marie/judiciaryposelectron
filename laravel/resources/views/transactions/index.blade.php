<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Weighbridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">⚖️ Weighbridge System</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="{{ route('dashboard') }}">Live Dashboard</a>
            <a class="nav-link active" href="{{ route('transactions.index') }}">Transactions</a>
            @can('manage form builder')
                <a class="nav-link" href="{{ route('forms.index') }}">Form Builder</a>
            @endcan
        </div>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">{{ Auth::user()->name }} ({{ Auth::user()->roles->pluck('name')->first() }})</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transaction Query & Filter Engine</h3>
        @can('export reporting')
            <a href="{{ route('transactions.export', request()->all()) }}" class="btn btn-outline-success fw-bold">
                📥 Export to CSV
            </a>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('transactions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quick Search</label>
                    <input type="text" id="qInput" name="q" class="form-control" placeholder="Search Ticket or Plate..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">License Plate</label>
                    <input type="text" name="license_plate" class="form-control" placeholder="Fuzzy plate..." value="{{ request('license_plate') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Inbound" {{ request('status') === 'Inbound' ? 'selected' : '' }}>Inbound (Pending)</option>
                        <option value="Outbound" {{ request('status') === 'Outbound' ? 'selected' : '' }}>Outbound (Completed)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Operator</label>
                    <select name="created_by" class="form-control">
                        <option value="">All Operators</option>
                        @foreach($operators as $op)
                            <option value="{{ $op->id }}" {{ request('created_by') == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>

                @if($activeForm && $activeForm->fields->count() > 0)
                    <div class="col-12 mt-2">
                        <small class="fw-bold text-muted">Custom Dynamic Field Filters:</small>
                        <div class="row g-2 mt-1">
                            @foreach($activeForm->fields as $field)
                                <div class="col-md-3">
                                    <input type="text" name="dynamic_fields[{{ $field->id }}]" class="form-control form-control-sm" placeholder="Filter by {{ $field->label }}..." value="{{ request('dynamic_fields.' . $field->id) }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Reset Filters</a>
                    <button type="submit" class="btn btn-primary fw-bold">Filter Results</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Results container -->
    <div id="tableContainer" class="card shadow-sm p-3 bg-white">
        @include('transactions._table', ['transactions' => $transactions])
    </div>
</div>

<script>
$(document).ready(function() {
    let timer;
    $('#qInput').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetchFilteredData();
        }, 300);
    });

    function fetchFilteredData() {
        let formData = $('#filterForm').serialize();
        $.ajax({
            url: "{{ route('transactions.index') }}",
            type: "GET",
            data: formData,
            success: function(response) {
                if(response.html) {
                    $('#tableContainer').html(response.html);
                }
            }
        });
    }
});
</script>
</body>
</html>
