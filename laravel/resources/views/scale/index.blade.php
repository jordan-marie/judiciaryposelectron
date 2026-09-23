@extends('layouts.app')

@section('content')
<!-- Header Bar with Form Switcher -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-body">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3">
                <i class="bi bi-display-fill fs-3"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0">Scale Terminal Console</h4>
                <div class="text-muted small">Operator Active Weighment View</div>
            </div>
        </div>

        <!-- Form Switcher Dropdown (Role Restricted) -->
        <div class="d-flex align-items-center gap-2 ms-auto" style="min-width: 300px;">
            <label for="form-switcher-select" class="form-label fw-bold text-nowrap mb-0">
                <i class="bi bi-journals text-primary me-1"></i> Active Form:
            </label>
            <select id="form-switcher-select" class="form-select form-select-lg fw-bold border-primary shadow-sm">
                @forelse($availableForms as $f)
                    <option value="{{ $f->id }}" {{ ($activeForm && $activeForm->id == $f->id) ? 'selected' : '' }}>
                        {{ $f->name }}
                    </option>
                @empty
                    <option value="">No Active Forms Assigned to Your Role</option>
                @endforelse
            </select>
        </div>
    </div>
</div>

@if(!$activeForm)
    <div class="alert alert-warning p-4 rounded-3 shadow-sm text-center">
        <i class="bi bi-exclamation-circle fs-1 text-warning d-block mb-2"></i>
        <h5 class="fw-bold">No Active Forms Available</h5>
        <p class="mb-0 text-muted">There are no active forms assigned to your role ({{ Auth::user()->roles->pluck('name')->join(', ') }}). Please contact an administrator.</p>
    </div>
@else

<!-- Active Recalled Transaction Banner (Hidden by default) -->
<div id="recalled-tx-banner" class="alert alert-info alert-dismissible fade show shadow-sm mb-4 d-none" role="alert">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <i class="bi bi-arrow-repeat me-2 fs-5"></i>
            <strong>Recalled In-Progress Transaction:</strong> <span id="recalled-code-text" class="font-monospace fw-bold"></span> (Completing 2nd Weighment)
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-cancel-recall">
            <i class="bi bi-x-circle me-1"></i> Switch to New Entry
        </button>
    </div>
</div>

<form id="scale-transaction-form" action="{{ route('scale.store') }}" method="POST">
    @csrf
    <input type="hidden" name="form_id" id="form_id_input" value="{{ $activeForm->id }}">
    <input type="hidden" name="transaction_id" id="transaction_id_input" value="">

    <div class="row g-4">
        <!-- Hardware Visual Cards & Pending Transactions Column -->
        <div class="col-12 col-xl-5">
            <!-- Hardware Card 1: Camera LPR Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase d-flex align-items-center gap-2">
                        <i class="bi bi-camera-video-fill text-info"></i> LPR Camera Feed #1
                    </h6>
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-circle-fill fs-7 me-1"></i> Live
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="camera-feed-container mb-3 position-relative">
                        <!-- Simulated Camera Feed View -->
                        <div class="text-center text-secondary">
                            <i class="bi bi-truck fs-1 d-block mb-1 text-opacity-50"></i>
                            <span class="small font-monospace text-uppercase text-muted">RTSP Camera Feed - Weighbridge Ramp Entrance</span>
                        </div>
                        <div class="camera-overlay-badge d-flex align-items-center gap-2">
                            <i class="bi bi-view-list"></i>
                            <span id="lpr-detected-text">PLATE: ABC-1234</span>
                        </div>
                    </div>

                    <!-- Manual Plate Override Input -->
                    <label for="plate_number" class="form-label fw-bold small text-muted">Detected Plate Number / Manual Override</label>
                    <div class="input-group">
                        <span class="input-group-text font-monospace bg-body-tertiary fw-bold"><i class="bi bi-card-text me-1"></i> PLATE</span>
                        <input type="text" class="form-control form-control-lg font-monospace fw-bold text-uppercase" id="plate_number" name="plate_number" value="ABC-1234" placeholder="e.g. ABC-1234">
                        <button type="button" class="btn btn-outline-info" id="btn-retrigger-lpr" title="Simulate Random LPR Scan">
                            <i class="bi bi-arrow-repeat"></i> Re-Scan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hardware Card 2: Weighbridge Indicator Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 text-success"></i> Weighbridge Indicator
                    </h6>
                    <span class="badge bg-success" id="weight-stability-badge">
                        <i class="bi bi-check-circle-fill me-1"></i> STABLE
                    </span>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="text-uppercase text-muted fw-bold small mb-2">Live Gross Scale Weight</div>
                    <div class="weight-display mb-3" id="live-weight-display">
                        028,450 KG
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="btn-zero-scale">
                            <i class="bi bi-dash-circle me-1"></i> Zero Scale
                        </button>
                        <button type="button" class="btn btn-success btn-lg fw-bold flex-grow-1 shadow-sm" id="btn-capture-gross">
                            <i class="bi bi-download me-1"></i> Capture Gross Weight
                        </button>
                    </div>
                </div>
            </div>

            <!-- In-Progress Transactions Panel (Pending 2nd Weighment) -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase d-flex align-items-center gap-2">
                        <i class="bi bi-hourglass-split text-warning"></i> In-Progress Weighments
                    </h6>
                    <span class="badge bg-warning text-dark fw-bold" id="pending-count-badge">
                        {{ $pendingTransactions->count() }} Pending
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">Code</th>
                                    <th>Plate</th>
                                    <th>1st Wt (KG)</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingTransactions as $pending)
                                    <tr id="pending-row-{{ $pending->id }}">
                                        <td class="ps-3 fw-bold font-monospace small text-primary">{{ $pending->transaction_code }}</td>
                                        <td class="fw-bold text-uppercase small">{{ $pending->plate_number ?? 'N/A' }}</td>
                                        <td class="small">{{ number_format($pending->gross_weight, 0) }}</td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-warning fw-bold btn-recall-tx" data-id="{{ $pending->id }}" title="Recall transaction to complete 2nd weighment">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Complete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No in-progress weighments pending.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Entry Card Column -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0" id="form-title-text">{{ $activeForm->name }}</h5>
                        <small class="text-muted" id="form-description-text">{{ $activeForm->description }}</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary fw-bold" id="form-field-count-badge">
                        {{ $activeForm->fields->count() }} Fields
                    </span>
                </div>

                <div class="card-body p-4">
                    <!-- Dynamic Form Inputs Container -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-secondary border-bottom pb-2 mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-input-cursor-text me-1"></i> Dynamic Form Inputs
                        </h6>
                        <div class="row" id="dynamic-fields-container">
                            @include('scale.partials.dynamic_fields', ['form' => $activeForm])
                        </div>
                    </div>

                    <!-- Real-Time Weight Calculation Section -->
                    <div class="p-3 bg-body-tertiary rounded-3 border mb-4">
                        <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-calculator me-1"></i> Weight Calculation Breakdown (KG)
                        </h6>

                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-4">
                                <label for="gross_weight" class="form-label small fw-bold">Gross Weight (KG)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-success" id="gross_weight" name="gross_weight" value="28450.00" required>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sync-gross" title="Sync with Indicator">
                                        <i class="bi bi-arrow-down-square"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tare_weight" class="form-label small fw-bold">Tare Weight (KG)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-warning" id="tare_weight" name="tare_weight" value="12100.00" required>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-capture-tare" title="Capture Tare">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="net_weight" class="form-label small fw-bold text-primary">Net Weight (KG)</label>
                                <input type="number" step="0.01" class="form-control form-control-lg fw-bold bg-primary bg-opacity-10 text-primary border-primary" id="net_weight" name="net_weight" value="16350.00" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Status Selector -->
                    <div class="row g-3 align-items-center mb-4">
                        <div class="col-12 col-md-6">
                            <label for="status" class="form-label fw-bold">Transaction Status</label>
                            <select class="form-select fw-semibold" id="status" name="status" required>
                                <option value="completed" selected>Completed (2nd Weight Done)</option>
                                <option value="in_progress">In Progress (1st Weight Pending)</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="button" class="btn btn-outline-secondary" id="btn-reset-form">Reset Fields</button>
                        <button type="submit" class="btn btn-primary btn-lg fw-bold px-4" id="btn-save-transaction">
                            <i class="bi bi-save-fill me-1"></i> Submit Weighment Entry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let simulatedLiveWeight = 28450;

        // 1. Dynamic Net Weight Calculation: Net = Gross - Tare
        function calculateNetWeight() {
            const gross = parseFloat($('#gross_weight').val()) || 0;
            const tare = parseFloat($('#tare_weight').val()) || 0;
            const net = Math.max(0, gross - tare);
            $('#net_weight').val(net.toFixed(2));
        }

        $('#gross_weight, #tare_weight').on('input change', function() {
            calculateNetWeight();
        });

        // 2. Form Switcher Dropdown AJAX Logic
        $('#form-switcher-select').on('change', function() {
            const formId = $(this).val();
            if (!formId) return;

            $('#form_id_input').val(formId);

            $.ajax({
                url: `/scale/forms/${formId}/fields`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#dynamic-fields-container').html(`
                        <div class="col-12 text-center py-4 text-muted">
                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                            Loading dynamic form fields...
                        </div>
                    `);
                },
                success: function(response) {
                    if (response.success) {
                        $('#form-title-text').text(response.form_name);
                        $('#form-description-text').text(response.description || '');
                        $('#form-field-count-badge').text(response.fields.length + ' Fields');
                        $('#dynamic-fields-container').html(response.html);
                    }
                },
                error: function(xhr) {
                    alert('Failed to load form fields. Please try again.');
                }
            });
        });

        // 3. Recall / Complete In-Progress Transaction AJAX Logic
        $(document).on('click', '.btn-recall-tx', function() {
            const txId = $(this).data('id');

            $.ajax({
                url: `/scale/transactions/${txId}/data`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#recalled-tx-banner').removeClass('d-none');
                    $('#recalled-code-text').text('Loading transaction #' + txId + '...');
                },
                success: function(response) {
                    if (response.success) {
                        const tx = response.transaction;
                        $('#transaction_id_input').val(tx.id);
                        $('#form_id_input').val(tx.form_id);

                        // Select corresponding form in dropdown
                        $('#form-switcher-select').val(tx.form_id);
                        if (response.form) {
                            $('#form-title-text').text(response.form.name);
                            $('#form-description-text').text(response.form.description || '');
                        }

                        // Populate weights and plate
                        $('#gross_weight').val(parseFloat(tx.gross_weight).toFixed(2));
                        $('#tare_weight').val(parseFloat(tx.tare_weight).toFixed(2));
                        if (tx.plate_number) {
                            $('#plate_number').val(tx.plate_number);
                            $('#lpr-detected-text').text('PLATE: ' + tx.plate_number);
                        }
                        calculateNetWeight();

                        // Set status to completed for 2nd weight
                        $('#status').val('completed');

                        // Render dynamic fields with recalled meta values
                        $('#dynamic-fields-container').html(response.html);

                        // Show banner
                        $('#recalled-code-text').text(tx.transaction_code);
                    }
                },
                error: function() {
                    alert('Error recalling transaction details.');
                }
            });
        });

        // Reset recalled transaction state back to new entry
        function resetRecalledState() {
            $('#transaction_id_input').val('');
            $('#recalled-tx-banner').addClass('d-none');
            $('#status').val('completed');
        }

        $('#btn-cancel-recall, #btn-reset-form').on('click', function() {
            resetRecalledState();
            $('#scale-transaction-form')[0].reset();
            calculateNetWeight();
        });

        // 4. Hardware Mock Interactions
        $('#btn-capture-gross').on('click', function() {
            $('#gross_weight').val(simulatedLiveWeight.toFixed(2));
            calculateNetWeight();

            $('#live-weight-display').addClass('border-success');
            setTimeout(() => $('#live-weight-display').removeClass('border-success'), 600);
        });

        $('#btn-sync-gross').on('click', function() {
            $('#gross_weight').val(simulatedLiveWeight.toFixed(2));
            calculateNetWeight();
        });

        $('#btn-capture-tare').on('click', function() {
            $('#tare_weight').val(simulatedLiveWeight.toFixed(2));
            calculateNetWeight();
        });

        $('#btn-zero-scale').on('click', function() {
            simulatedLiveWeight = 0;
            $('#live-weight-display').text('00,000 KG');
            $('#gross_weight').val('0.00');
            calculateNetWeight();
        });

        // LPR Re-scan simulation
        const samplePlates = ['ABC-1234', 'XYZ-9876', 'KGL-4412', 'MH-04-AB-9012', 'TEX-8821'];
        $('#btn-retrigger-lpr').on('click', function() {
            const randomPlate = samplePlates[Math.floor(Math.random() * samplePlates.length)];
            $('#lpr-detected-text').text('PLATE: ' + randomPlate);
            $('#plate_number').val(randomPlate);
        });

        // 5. Form Submit AJAX
        $('#scale-transaction-form').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const actionUrl = form.attr('action');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#btn-save-transaction').prop('disabled', true).html(`
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                    `);
                },
                success: function(response) {
                    $('#btn-save-transaction').prop('disabled', false).html(`
                        <i class="bi bi-save-fill me-1"></i> Submit Weighment Entry
                    `);

                    if (response.success) {
                        alert(response.message);
                        window.location.href = `/transactions/${response.transaction.id}`;
                    }
                },
                error: function(xhr) {
                    $('#btn-save-transaction').prop('disabled', false).html(`
                        <i class="bi bi-save-fill me-1"></i> Submit Weighment Entry
                    `);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errStr = 'Validation Errors:\n';
                        for (let k in errors) {
                            errStr += `- ${errors[k].join(', ')}\n`;
                        }
                        alert(errStr);
                    } else {
                        alert('An error occurred while saving the transaction.');
                    }
                }
            });
        });
    });
</script>
@endpush
