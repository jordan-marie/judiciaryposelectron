@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Roles & Permissions Management</h3>
        <p class="text-muted small mb-0">Manage system roles and assign role permissions to users</p>
    </div>
</div>

<div class="row g-4">
    <!-- Roles List Card -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">System Roles</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($roles as $role)
                        <li class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary">{{ $role->name }}</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $role->users_count }} Users</span>
                        </li>
                    @endforeach
                </ul>
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
                    <button type="submit" class="btn btn-primary fw-bold w-100">Create Role</button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Role Assignment Table -->
    <div class="col-12 col-lg-7">
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
