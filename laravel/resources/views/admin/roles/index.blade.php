@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Roles & Form Permissions Management</h3>
        <p class="text-muted small mb-0">Manage system roles, assign form access permissions per role, and assign user roles</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Roles & Form Permission Assignment Cards -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0">System Roles & Form Permissions</h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="rolesAccordion">
                    @foreach($roles as $role)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading_{{ $role->id }}">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $role->id }}" aria-expanded="false" aria-controls="collapse_{{ $role->id }}">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <span class="text-primary">{{ $role->name }}</span>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $role->forms->count() }} Forms Assigned</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse_{{ $role->id }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $role->id }}" data-bs-parent="#rolesAccordion">
                                <div class="accordion-body bg-body-tertiary">
                                    <form action="{{ route('admin.roles.update-forms', $role->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-uppercase text-secondary">Form Access Permissions</label>
                                            @php
                                                $assignedFormIds = $role->forms->pluck('id')->toArray();
                                            @endphp
                                            @foreach($forms as $f)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="forms[]" value="{{ $f->id }}" id="role_{{ $role->id }}_form_{{ $f->id }}" {{ in_array($f->id, $assignedFormIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="role_{{ $role->id }}_form_{{ $f->id }}">
                                                        {{ $f->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                            <i class="bi bi-save me-1"></i> Save Form Permissions
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Add New Role Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0">Create New Role</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="role_name" class="form-label fw-semibold">Role Name</label>
                        <input type="text" class="form-control" id="role_name" name="name" placeholder="e.g. Weighbridge Auditor" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Initial Form Access</label>
                        @foreach($forms as $f)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="forms[]" value="{{ $f->id }}" id="new_role_form_{{ $f->id }}">
                                <label class="form-check-label small" for="new_role_form_{{ $f->id }}">
                                    {{ $f->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold w-100">Create Role</button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Role Assignment Table -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="fw-bold mb-0">User Role Assignments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Email</th>
                                <th>Assigned Roles</th>
                                <th class="text-end pe-4">Update Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $u->name }}</td>
                                    <td class="small text-muted">{{ $u->email }}</td>
                                    <td>
                                        @foreach($u->roles as $r)
                                            <span class="badge bg-secondary-subtle text-secondary me-1">{{ $r->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.users.update-roles', $u->id) }}" method="POST" class="d-flex justify-content-end gap-1">
                                            @csrf
                                            <select name="roles[]" class="form-select form-select-sm" style="width: auto;">
                                                @foreach($roles as $r)
                                                    <option value="{{ $r->name }}" {{ $u->hasRole($r->name) ? 'selected' : '' }}>
                                                        {{ $r->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
