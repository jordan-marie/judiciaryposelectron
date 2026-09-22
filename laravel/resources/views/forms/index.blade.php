<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Form Builder - Weighbridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">⚖️ Weighbridge System</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="{{ route('dashboard') }}">Live Dashboard</a>
            <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
            @can('manage form builder')
                <a class="nav-link active" href="{{ route('forms.index') }}">Form Builder</a>
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

<div class="container">
    <h3 class="mb-4">Dynamic Form Builder</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @foreach($forms as $form)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $form->title }}</h5>
                <span class="badge bg-light text-dark">{{ $form->is_active ? 'Active Form' : 'Inactive' }}</span>
            </div>
            <div class="card-body">
                <h6>Configured Fields</h6>
                <table class="table table-bordered align-middle mt-3">
                    <thead class="table-secondary">
                        <tr>
                            <th>Sort Order</th>
                            <th>Field Label</th>
                            <th>Field Key / Name</th>
                            <th>Field Type</th>
                            <th>Required?</th>
                            <th>Options (for select)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($form->fields as $field)
                            <tr>
                                <td>{{ $field->sort_order }}</td>
                                <td><strong>{{ $field->label }}</strong></td>
                                <td><code>{{ $field->field_name }}</code></td>
                                <td><span class="badge bg-info text-dark">{{ strtoupper($field->field_type) }}</span></td>
                                <td>
                                    @if($field->is_required)
                                        <span class="badge bg-danger">Required</span>
                                    @else
                                        <span class="badge bg-secondary">Optional</span>
                                    @endif
                                </td>
                                <td>
                                    @if($field->options_json)
                                        <small>{{ implode(', ', $field->options_json) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('forms.fields.delete', $field->id) }}" method="POST" onsubmit="return confirm('Delete field?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No custom fields created for this form.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr class="my-4">

                <h6>Add New Form Field</h6>
                <form action="{{ route('forms.fields.create', $form->id) }}" method="POST" class="row g-3 mt-1">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Field Label</label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. Container No" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Field Name Key</label>
                        <input type="text" name="field_name" class="form-control" placeholder="e.g. container_no" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Field Type</label>
                        <select name="field_type" class="form-control" required>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="select">Select Dropdown</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="reqCheck_{{ $form->id }}">
                            <label class="form-check-label" for="reqCheck_{{ $form->id }}">Required Field</label>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label">Select Options <small class="text-muted">(Comma-separated, for select fields only)</small></label>
                        <input type="text" name="options" class="form-control" placeholder="Option 1, Option 2, Option 3">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Add Field</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
