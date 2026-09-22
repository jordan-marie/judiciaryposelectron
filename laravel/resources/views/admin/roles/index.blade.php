@extends('layouts.app')

@section('title', 'Role & Permission Management')
@section('header-title', 'Role & Permission Management')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- System Roles & Permissions Matrix -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i> System Roles & Permissions</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Role
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Role Name</th>
                                    <th>Assigned Permissions</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $role->name }}</span>
                                    </td>
                                    <td>
                                        @foreach($role->permissions as $perm)
                                            <span class="badge bg-secondary mb-1">{{ $perm->name }}</span>
                                        @endforeach
                                        @if($role->permissions->isEmpty())
                                            <span class="text-muted fst-italic fs-7">No permissions assigned</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary edit-role-permissions-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRoleModal"
                                                data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->name }}"
                                                data-permissions="{{ json_encode($role->permissions->pluck('name')) }}">
                                            <i class="bi bi-pencil-square"></i> Permissions
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Role Assignment Panel -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i> User Role Assignment</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Current Role(s)</th>
                                    <th class="text-end">Assign</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted fs-7">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $uRole)
                                            <span class="badge bg-info text-dark">{{ $uRole->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary edit-user-roles-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserRolesModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-roles="{{ json_encode($user->roles->pluck('name')) }}">
                                            <i class="bi bi-person-gear"></i>
                                        </button>
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
</div>

<!-- Modal: Create New Role -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('roles.storeRole') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label fw-semibold">Role Name</label>
                        <input type="text" class="form-control" id="roleName" name="name" placeholder="e.g. Inspector" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Permissions</label>
                        @foreach($permissions as $perm)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_create_{{ $perm->id }}">
                                <label class="form-check-label" for="perm_create_{{ $perm->id }}">
                                    {{ $perm->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Role Permissions -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRolePermissionsForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Role Permissions: <span id="modalRoleName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Permissions</label>
                        @foreach($permissions as $perm)
                            <div class="form-check">
                                <input class="form-check-input role-perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_edit_{{ $perm->id }}">
                                <label class="form-check-label" for="perm_edit_{{ $perm->id }}">
                                    {{ $perm->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Permissions</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User Roles -->
<div class="modal fade" id="editUserRolesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editUserRolesForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Roles to <span id="modalUserName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Roles</label>
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input user-role-checkbox" type="checkbox" name="roles[]" value="{{ $role->name }}" id="user_role_{{ $role->id }}">
                                <label class="form-check-label fw-semibold" for="user_role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User Roles</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Edit Role Permissions Modal Populator
        $('.edit-role-permissions-btn').on('click', function() {
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');
            const rolePerms = $(this).data('permissions') || [];

            $('#modalRoleName').text(roleName);
            $('#editRolePermissionsForm').attr('action', '/admin/roles/' + roleId + '/permissions');

            $('.role-perm-checkbox').prop('checked', false);
            rolePerms.forEach(function(perm) {
                $('.role-perm-checkbox[value="' + perm + '"]').prop('checked', true);
            });
        });

        // Edit User Roles Modal Populator
        $('.edit-user-roles-btn').on('click', function() {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');
            const userRoles = $(this).data('user-roles') || [];

            $('#modalUserName').text(userName);
            $('#editUserRolesForm').attr('action', '/admin/users/' + userId + '/roles');

            $('.user-role-checkbox').prop('checked', false);
            userRoles.forEach(function(role) {
                $('.user-role-checkbox[value="' + role + '"]').prop('checked', true);
            });
        });
    });
</script>
@endpush
