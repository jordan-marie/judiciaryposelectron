@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Edit Dynamic Form: {{ $form->name }}</h3>
        <p class="text-muted small mb-0">Modify configuration, role permissions, and dynamic field order</p>
    </div>
    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Forms List
    </a>
</div>

<form action="{{ route('admin.forms.update', $form->id) }}" method="POST" id="form-builder-edit-form">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Form Settings Side Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-body py-3 border-bottom">
                    <h5 class="fw-bold mb-0">1. Form Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Form Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $form->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $form->description) }}</textarea>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $form->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">Form Status (Active / Ready for Scale Console)</label>
                    </div>
                </div>
            </div>

            <!-- Role Assignment Pivot Checkboxes -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body py-3 border-bottom">
                    <h5 class="fw-bold mb-0">2. Permitted User Roles <span class="text-danger">*</span></h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Scale operators will only see this form if their assigned role is selected below.</p>
                    @php
                        $assignedRoleIds = $form->roles->pluck('id')->toArray();
                    @endphp
                    @foreach($roles as $role)
                        <div class="form-check mb-2">
                            <input class="form-check-input role-checkbox" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ in_array($role->id, $assignedRoleIds) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                    @error('roles')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Dynamic Field Builder Area -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">3. Dynamic Form Fields</h5>
                        <small class="text-muted">Drag fields using the handle to reorder</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="add-field-btn">
                        <i class="bi bi-plus-circle me-1"></i> Add New Field
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="fields-sortable-list" class="d-flex flex-column gap-3">
                        <!-- Dynamic Field Item template inserted via JS -->
                    </div>

                    <div id="no-fields-warning" class="text-center py-4 text-muted border border-dashed rounded-3 d-none">
                        <i class="bi bi-card-list fs-1 text-secondary"></i>
                        <p class="mb-0 mt-2">No dynamic fields added yet. Click <strong>"Add New Field"</strong> above.</p>
                    </div>
                </div>
                <div class="card-footer bg-body py-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Update Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Field Template for JavaScript -->
<template id="field-row-template">
    <div class="card border border-secondary border-opacity-25 shadow-sm field-item">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="btn btn-sm btn-light cursor-grab drag-handle p-1 text-secondary" title="Drag to reorder">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </span>
                <span class="fw-bold text-primary field-index-title">Field #1</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-field-btn" title="Remove Field">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold">Field Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm field-label-input" name="fields[INDEX][label]" placeholder="e.g. Customer Name" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">Field Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm field-type-select" name="fields[INDEX][field_type]" required>
                        <option value="text">Text Input</option>
                        <option value="number">Number Input</option>
                        <option value="select">Select Dropdown</option>
                        <option value="datetime">Date & Time</option>
                        <option value="checkbox">Checkbox (Yes/No)</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex align-items-end mb-1">
                    <div class="form-check">
                        <input class="form-check-input field-required-checkbox" type="checkbox" name="fields[INDEX][is_required]" value="1" id="req_INDEX">
                        <label class="form-check-label small fw-bold" for="req_INDEX">
                            Required Field
                        </label>
                    </div>
                </div>

                <!-- Select Dropdown Options Container -->
                <div class="col-12 options-container d-none">
                    <label class="form-label small fw-bold">Dropdown Options (Comma separated)</label>
                    <input type="text" class="form-control form-control-sm field-options-input" name="fields[INDEX][options]" placeholder="Option 1, Option 2, Option 3">
                    <div class="form-text extra-small">Enter dropdown choices separated by commas (e.g. Municipal, Industrial, Recyclable).</div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const existingFields = @json($form->fields);

        const sortableContainer = document.getElementById('fields-sortable-list');
        const sortable = new Sortable(sortableContainer, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                reindexFields();
            }
        });

        function updateWarningState() {
            if ($('#fields-sortable-list .field-item').length === 0) {
                $('#no-fields-warning').removeClass('d-none');
            } else {
                $('#no-fields-warning').addClass('d-none');
            }
        }

        function reindexFields() {
            $('#fields-sortable-list .field-item').each(function(index) {
                const idx = index;
                $(this).find('.field-index-title').text('Field #' + (idx + 1));

                $(this).find('.field-label-input').attr('name', `fields[${idx}][label]`);
                $(this).find('.field-type-select').attr('name', `fields[${idx}][field_type]`);
                $(this).find('.field-required-checkbox').attr('name', `fields[${idx}][is_required]`).attr('id', `req_${idx}`);
                $(this).find('.field-required-checkbox').next('label').attr('for', `req_${idx}`);
                $(this).find('.field-options-input').attr('name', `fields[${idx}][options]`);
            });
            updateWarningState();
        }

        function addFieldRow(label = '', type = 'text', isRequired = false, options = '') {
            const templateHtml = $('#field-row-template').html();
            const $newRow = $(templateHtml);

            if (label) $newRow.find('.field-label-input').val(label);
            $newRow.find('.field-type-select').val(type);
            if (isRequired) $newRow.find('.field-required-checkbox').prop('checked', true);
            if (options) $newRow.find('.field-options-input').val(options);

            if (type === 'select') {
                $newRow.find('.options-container').removeClass('d-none');
            }

            $('#fields-sortable-list').append($newRow);
            reindexFields();
        }

        // Load existing fields
        if (existingFields && existingFields.length > 0) {
            existingFields.forEach(f => {
                let optsStr = '';
                if (Array.isArray(f.options)) {
                    optsStr = f.options.join(', ');
                } else if (typeof f.options === 'string') {
                    optsStr = f.options;
                }
                addFieldRow(f.label, f.field_type, f.is_required, optsStr);
            });
        }

        // Add Field Button click
        $('#add-field-btn').on('click', function() {
            addFieldRow();
        });

        // Toggle Options container when field type is select
        $(document).on('change', '.field-type-select', function() {
            const val = $(this).val();
            const $optionsContainer = $(this).closest('.card-body').find('.options-container');
            if (val === 'select') {
                $optionsContainer.removeClass('d-none');
            } else {
                $optionsContainer.addClass('d-none');
            }
        });

        // Remove field button click
        $(document).on('click', '.remove-field-btn', function() {
            $(this).closest('.field-item').remove();
            reindexFields();
        });
    });
</script>
@endpush
