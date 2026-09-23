@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dynamic Form Builder</h3>
        <p class="text-muted small mb-0">Create custom dynamic weighment forms and assign role access permissions</p>
    </div>
    <a href="{{ route('admin.forms.create') }}" class="btn btn-primary fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Create New Form
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Form Name</th>
                        <th>Slug</th>
                        <th>Assigned Roles</th>
                        <th>Fields Count</th>
                        <th>Transactions Logged</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        <tr>
                            <td class="ps-4 fw-bold">
                                {{ $form->name }}
                                @if($form->description)
                                    <div class="text-muted small fw-normal">{{ Str::limit($form->description, 60) }}</div>
                                @endif
                            </td>
                            <td><code class="text-primary">{{ $form->slug }}</code></td>
                            <td>
                                @forelse($form->roles as $role)
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ $role->name }}</span>
                                @empty
                                    <span class="badge bg-warning-subtle text-warning">No roles assigned</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info fw-bold">{{ $form->fields_count }} fields</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $form->transactions_count }}</span>
                            </td>
                            <td>
                                @if($form->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('admin.forms.toggle-status', $form->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary" title="Toggle Active Status">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.forms.edit', $form->id) }}" class="btn btn-outline-primary" title="Edit Form">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.forms.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this form?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Form">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No dynamic forms found. Click "Create New Form" to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
