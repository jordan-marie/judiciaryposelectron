@extends('layouts.app')

@section('title', 'QR Code Generator - Weighbridge Management System')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 font-weight-bold mb-1"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Transaction QR Code Generator</h2>
        <p class="text-secondary mb-0">Select a form, fill sample data, and generate a QR code for instant auto-fill on the Scale Terminal.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Generator Form Input -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-body-tertiary fw-semibold py-3">
                <i class="bi bi-ui-checks me-1"></i> Form Data Configuration
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Target Dynamic Form <span class="text-danger">*</span></label>
                    <select id="qrFormSelect" class="form-select form-select-lg">
                        <option value="">-- Choose Dynamic Form --</option>
                        @foreach($forms as $form)
                            <option value="{{ $form->id }}" data-slug="{{ $form->slug }}">
                                {{ $form->name }} ({{ $form->fields->count() }} dynamic fields)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Vehicle License Plate Number</label>
                    <input type="text" id="qrPlateNumber" class="form-control text-uppercase" placeholder="e.g. ABC-1234">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Gross Weight (KG)</label>
                        <input type="number" id="qrGrossWeight" class="form-control" placeholder="e.g. 28450" step="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tare Weight (KG)</label>
                        <input type="number" id="qrTareWeight" class="form-control" placeholder="e.g. 12100" step="10">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold mb-3"><i class="bi bi-sliders me-1"></i> Dynamic Form Fields</h5>
                <div id="dynamicQrFieldsContainer">
                    <div class="alert alert-info border-0 rounded-3 text-center py-4">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        Please select a dynamic form above to populate field inputs.
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <button id="btnGenerateQr" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" disabled>
                        <i class="bi bi-qr-code me-2"></i> Generate QR Code JSON
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated QR Preview & JSON Payload -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-3 mb-4 text-center">
            <div class="card-header bg-body-tertiary fw-semibold py-3 text-start">
                <i class="bi bi-qr-code me-1"></i> Generated QR Code Preview
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                <div id="qrcodeCanvasContainer" class="p-3 bg-white rounded border d-inline-block my-3 shadow-sm" style="min-width: 220px; min-height: 220px;">
                    <div id="qrcodePlaceholder" class="text-secondary py-5">
                        <i class="bi bi-qr-code fs-1 d-block mb-2 text-muted"></i>
                        <span class="small">QR Code will render here</span>
                    </div>
                    <div id="qrcode"></div>
                </div>

                <div class="w-100 text-start mt-3">
                    <label class="form-label fw-bold small text-muted">JSON PAYLOAD OUTPUT</label>
                    <textarea id="qrPayloadJson" class="form-control font-monospace fs-7 bg-body-tertiary" rows="6" readonly placeholder="Raw JSON payload will appear here..."></textarea>
                </div>

                <div class="mt-3 w-100 d-flex gap-2">
                    <button id="btnCopyJson" class="btn btn-outline-secondary btn-sm flex-fill" disabled>
                        <i class="bi bi-clipboard me-1"></i> Copy JSON
                    </button>
                    <button id="btnPrintQr" class="btn btn-outline-primary btn-sm flex-fill" onclick="window.print()" disabled>
                        <i class="bi bi-printer me-1"></i> Print QR Card
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QRCode.js library for HTML5 canvas QR rendering -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    const formsData = @json($forms);
    let qrcodeObj = null;

    $('#qrFormSelect').on('change', function() {
        const formId = parseInt($(this).val());
        const container = $('#dynamicQrFieldsContainer');
        container.empty();

        if (!formId) {
            container.html(`
                <div class="alert alert-info border-0 rounded-3 text-center py-4">
                    <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                    Please select a dynamic form above to populate field inputs.
                </div>
            `);
            $('#btnGenerateQr').prop('disabled', true);
            return;
        }

        const selectedForm = formsData.find(f => f.id === formId);
        if (!selectedForm || !selectedForm.fields || selectedForm.fields.length === 0) {
            container.html(`
                <div class="alert alert-warning border-0 rounded-3 text-center py-3">
                    This form does not have dynamic custom fields configured.
                </div>
            `);
        } else {
            selectedForm.fields.forEach(field => {
                let optionsHtml = '';
                if (field.options && field.options.length) {
                    field.options.forEach(opt => {
                        optionsHtml += `<option value="${opt}">${opt}</option>`;
                    });
                }

                let inputControl = '';
                if (field.field_type === 'select') {
                    inputControl = `<select class="form-select dynamic-qr-field" data-field-name="${field.field_name}">
                        <option value="">-- Select ${field.label} --</option>
                        ${optionsHtml}
                    </select>`;
                } else if (field.field_type === 'checkbox') {
                    inputControl = `
                        <div class="form-check mt-2">
                            <input class="form-check-input dynamic-qr-field" type="checkbox" data-field-name="${field.field_name}" value="1" id="qr_field_${field.id}">
                            <label class="form-check-label" for="qr_field_${field.id}">Yes / Enabled</label>
                        </div>`;
                } else if (field.field_type === 'number') {
                    inputControl = `<input type="number" class="form-control dynamic-qr-field" data-field-name="${field.field_name}" placeholder="Enter ${field.label}">`;
                } else if (field.field_type === 'datetime') {
                    inputControl = `<input type="datetime-local" class="form-control dynamic-qr-field" data-field-name="${field.field_name}">`;
                } else {
                    inputControl = `<input type="text" class="form-control dynamic-qr-field" data-field-name="${field.field_name}" placeholder="Enter ${field.label}">`;
                }

                container.append(`
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small">${field.label} ${field.is_required ? '<span class="text-danger">*</span>' : ''}</label>
                        ${inputControl}
                    </div>
                `);
            });
        }

        $('#btnGenerateQr').prop('disabled', false);
    });

    $('#btnGenerateQr').on('click', function() {
        const formId = $('#qrFormSelect').val();
        if (!formId) return;

        const selectedOption = $('#qrFormSelect option:selected');
        const formSlug = selectedOption.data('slug');

        const payload = {
            form_id: parseInt(formId),
            form_slug: formSlug,
            plate_number: $('#qrPlateNumber').val().trim().toUpperCase(),
            gross_weight: $('#qrGrossWeight').val() ? parseFloat($('#qrGrossWeight').val()) : null,
            tare_weight: $('#qrTareWeight').val() ? parseFloat($('#qrTareWeight').val()) : null,
            fields: {}
        };

        $('.dynamic-qr-field').each(function() {
            const fieldName = $(this).data('field-name');
            let val = null;
            if ($(this).attr('type') === 'checkbox') {
                val = $(this).is(':checked') ? '1' : '0';
            } else {
                val = $(this).val();
            }
            if (val !== null && val !== '') {
                payload.fields[fieldName] = val;
            }
        });

        const jsonStr = JSON.stringify(payload, null, 2);
        $('#qrPayloadJson').val(jsonStr);

        $('#qrcodePlaceholder').addClass('d-none');
        $('#qrcode').empty();

        qrcodeObj = new QRCode(document.getElementById("qrcode"), {
            text: JSON.stringify(payload),
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        $('#btnCopyJson, #btnPrintQr').prop('disabled', false);
    });

    $('#btnCopyJson').on('click', function() {
        const copyText = document.getElementById("qrPayloadJson");
        copyText.select();
        navigator.clipboard.writeText(copyText.value);
        alert('QR Code JSON payload copied to clipboard!');
    });
});
</script>
@endpush
