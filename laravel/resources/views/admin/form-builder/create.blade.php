@extends('layouts.app')

@section('title', 'Create Transaction Form Template')
@section('header-title', 'Create Transaction Form Template')

@section('content')
<div class="container-fluid">
    <form action="{{ route('form-builder.store') }}" method="POST" id="formBuilderForm">
        @csrf
        <div class="row g-4">
            <!-- Form Metadata & Controls -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-gear me-2 text-primary"></i> Template Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="formName" class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="formName" name="name" value="{{ old('name', 'Standard Weighbridge Form') }}" placeholder="e.g. Coal & Logistics Scale Form" required>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold" for="isActive">Set as Active Weighbridge Form</label>
                            <div class="form-text fs-7">Only one form template can be active for scale operations at a time.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Save Form Template
                            </button>
                            <a href="{{ route('form-builder.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Fields Canvas -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-nested me-2 text-primary"></i> Form Fields Canvas</h5>
                        <button type="button" class="btn btn-sm btn-success fw-bold" id="addFieldBtn">
                            <i class="bi bi-plus-lg me-1"></i> Add Custom Field
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted fs-7 mb-3">Drag rows to reorder fields. Standard weight fields (Gross, Tare, Net) and Plate Number are system defaults and handled automatically.</p>

                        <div id="fieldsContainer">
                            <!-- Field Row Items render here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template for Field Row -->
<template id="fieldRowTemplate">
    <div class="card mb-3 border field-row shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="drag-handle text-muted cursor-move p-1" style="cursor: grab;"><i class="bi bi-grip-vertical fs-5"></i></span>
                <span class="fw-bold text-primary field-index-badge">Field #</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-field-btn"><i class="bi bi-trash"></i> Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fs-7 fw-semibold mb-1">Field Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm field-label-input" name="fields[INDEX][label]" placeholder="e.g. Driver Name" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7 fw-semibold mb-1">Field Identifier Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm field-name-input" name="fields[INDEX][field_name]" placeholder="driver_name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold mb-1">Field Type</label>
                    <select class="form-select form-select-sm field-type-select" name="fields[INDEX][field_type]">
                        <option value="text">Text Input</option>
                        <option value="number">Numeric Input</option>
                        <option value="select">Select Dropdown</option>
                        <option value="datetime">Date & Time</option>
                        <option value="checkbox">Checkbox (Yes/No)</option>
                    </select>
                </div>
                <div class="col-12 select-options-wrapper d-none">
                    <label class="form-label fs-7 fw-semibold mb-1">Dropdown Options (Comma separated)</label>
                    <input type="text" class="form-control form-control-sm field-options-input" name="fields[INDEX][options]" placeholder="Option 1, Option 2, Option 3">
                </div>
                <div class="col-12">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input field-required-checkbox" type="checkbox" name="fields[INDEX][is_required]" value="1" id="req_INDEX">
                        <label class="form-check-label fs-7 fw-semibold" for="req_INDEX">Required Field</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let fieldIndex = 0;
        const container = document.getElementById('fieldsContainer');

        // Initialize SortableJS
        new Sortable(container, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                reindexFields();
            }
        });

        function addFieldRow(data = null) {
            const templateHtml = $('#fieldRowTemplate').html();
            const rowHtml = templateHtml.replace(/INDEX/g, fieldIndex);
            const $row = $(rowHtml);

            if (data) {
                $row.find('.field-label-input').val(data.label);
                $row.find('.field-name-input').val(data.field_name);
                $row.find('.field-type-select').val(data.field_type);
                if (data.is_required) {
                    $row.find('.field-required-checkbox').prop('checked', true);
                }
                if (data.field_type === 'select') {
                    $row.find('.select-options-wrapper').removeClass('d-none');
                    $row.find('.field-options-input').val(data.options);
                }
            }

            $('#fieldsContainer').append($row);
            fieldIndex++;
            reindexFields();
        }

        function reindexFields() {
            $('#fieldsContainer .field-row').each(function(idx) {
                $(this).find('.field-index-badge').text('Field #' + (idx + 1));
            });
        }

        // Auto Slugify Label into Field Name
        $(document).on('input', '.field-label-input', function() {
            const labelVal = $(this).val();
            const slugified = labelVal.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
            $(this).closest('.card-body').find('.field-name-input').val(slugified);
        });

        // Toggle options input on select field type
        $(document).on('change', '.field-type-select', function() {
            const optionsWrapper = $(this).closest('.card-body').find('.select-options-wrapper');
            if ($(this).val() === 'select') {
                optionsWrapper.removeClass('d-none');
            } else {
                optionsWrapper.addClass('d-none');
            }
        });

        // Remove row
        $(document).on('click', '.remove-field-btn', function() {
            if ($('#fieldsContainer .field-row').length <= 1) {
                alert('At least one field is required in the form template.');
                return;
            }
            $(this).closest('.field-row').remove();
            reindexFields();
        });

        $('#addFieldBtn').on('click', function() {
            addFieldRow();
        });

        // Add default starting fields
        addFieldRow({ label: 'Driver Name', field_name: 'driver_name', field_type: 'text', is_required: true });
        addFieldRow({ label: 'Transporter', field_name: 'transporter', field_type: 'text', is_required: true });
        addFieldRow({ label: 'Material Type', field_name: 'material_type', field_type: 'select', options: 'Coal, Gravel, Sand, Steel, Grain', is_required: true });
        addFieldRow({ label: 'Remarks', field_name: 'remarks', field_type: 'text', is_required: false });
    });
</script>
@endpush
