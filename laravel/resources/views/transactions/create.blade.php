@extends('layouts.app')

@section('title', 'Scale Operator Terminal - WeighSys')
@section('header-title', 'Weighbridge Scale Operator Terminal')

@section('content')
<div class="container-fluid">
    <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
        @csrf
        <input type="hidden" name="transaction_code" value="{{ $transactionCode }}">

        <!-- Top Operator Control Bar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary fs-6 font-monospace px-3 py-2 shadow-sm">{{ $transactionCode }}</span>
                    <span class="text-muted fs-7">Operator: <strong class="text-body fw-bold">{{ Auth::user()->name }}</strong></span>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="status" id="statusCompleted" value="completed" checked>
                        <label class="form-check-label fw-bold text-success" for="statusCompleted"><i class="bi bi-check-circle-fill me-1"></i> Completed</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="status" id="statusInProgress" value="in_progress">
                        <label class="form-check-label fw-bold text-warning" for="statusInProgress"><i class="bi bi-hourglass-split me-1"></i> In Progress (First Weighment)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Hardware Interfacing (Camera LPR & Digital Scale Indicator) -->
            <div class="col-lg-6">
                <!-- Hardware Card 1: Camera LPR Interfacing -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-camera-reels-fill text-primary fs-5"></i> LPR Camera Integration (Mock UI)
                        </h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">
                            <i class="bi bi-record-fill me-1 text-danger animate-pulse"></i> LIVE FEED
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="lpr-camera-viewport mb-3">
                            <div class="lpr-viewfinder-overlay"></div>
                            <div class="text-center position-relative z-1">
                                <i class="bi bi-truck display-2 opacity-20 text-light"></i>
                                <p class="mb-0 fs-8 text-muted uppercase tracking-wider mt-1 fw-semibold">Lane 1 - Optical Character Recognition Active</p>
                            </div>
                            <div class="lpr-plate-badge" id="lprVisualBadge">B 1234 ABC</div>
                        </div>

                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text fw-extrabold bg-warning text-dark border-0"><i class="bi bi-badge-ad me-1"></i> Plate</span>
                                    <input type="text" class="form-control fw-extrabold font-monospace text-uppercase fs-5" id="plateNumberInput" name="plate_number" value="B 1234 ABC" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <button type="button" class="btn btn-outline-primary w-100 fw-bold" id="simLprDetectionBtn">
                                    <i class="bi bi-arrow-repeat me-1"></i> Detect Vehicle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hardware Card 2: Digital Weighbridge Scale Indicator -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-display-fill text-primary fs-5"></i> Digital Scale Indicator Readout
                        </h6>
                        <span id="scaleStabilityBadge" class="badge bg-success border border-success rounded-pill px-3 py-1.5 fw-bold">STABLE</span>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="text-muted fs-8 text-uppercase mb-2 fw-bold tracking-wider">Scale Platform Sensor Feed</div>
                        <div class="digital-weight-display mb-4" id="liveWeightDisplay">28,500 KG</div>

                        <!-- Quick Weight Capture Actions -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <button type="button" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="captureGrossBtn">
                                    <i class="bi bi-download fs-5"></i> Capture Gross
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-warning text-dark btn-lg w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="captureTareBtn">
                                    <i class="bi bi-download fs-5"></i> Capture Tare
                                </button>
                            </div>
                        </div>

                        <!-- Hardware Simulation Controls -->
                        <div class="border-top pt-3 text-start">
                            <small class="text-muted fw-bold d-block mb-2 fs-8 uppercase tracking-wider">Hardware Sensor Simulation Controls:</small>
                            <div class="d-flex flex-wrap gap-1.5">
                                <button type="button" class="btn btn-xs btn-outline-secondary sim-weight-btn" data-weight="28500">28,500 KG (Loaded)</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary sim-weight-btn" data-weight="32100">32,100 KG (Heavy)</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary sim-weight-btn" data-weight="12100">12,100 KG (Tare)</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary sim-weight-btn" data-weight="0">0 KG (Zero)</button>
                                <button type="button" class="btn btn-xs btn-outline-warning" id="toggleStabilityBtn">Toggle Unstable</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Calculation Engine & Dynamic Fields Canvas -->
            <div class="col-lg-6">
                <!-- Weight Calculation Engine Summary Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-calculator-fill text-primary fs-5"></i> Weighment Calculations Engine
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="grossWeightInput" class="form-label fw-bold text-success fs-7">Gross Weight <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control fw-bold fs-5 text-end" id="grossWeightInput" name="gross_weight" value="28500.00" required>
                                    <span class="input-group-text fs-8 fw-bold">kg</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tareWeightInput" class="form-label fw-bold text-warning fs-7">Tare Weight <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control fw-bold fs-5 text-end" id="tareWeightInput" name="tare_weight" value="12100.00" required>
                                    <span class="input-group-text fs-8 fw-bold">kg</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="netWeightInput" class="form-label fw-bold text-primary fs-7">Net Weight</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control fw-bold fs-5 text-end bg-primary bg-opacity-10 text-primary border-primary" id="netWeightInput" name="net_weight" value="16400.00" readonly>
                                    <span class="input-group-text fs-8 fw-bold bg-primary bg-opacity-10 text-primary border-primary">kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Form Fields Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-card-checklist text-primary fs-5"></i> {{ $activeForm ? $activeForm->name : 'Dynamic Transaction Details' }}
                        </h6>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill fs-8">Dynamic Field Canvas</span>
                    </div>
                    <div class="card-body">
                        @if($activeForm && $activeForm->fields->count() > 0)
                            <div class="row g-3">
                                @foreach($activeForm->fields as $field)
                                    <div class="col-12 {{ $field->field_type === 'checkbox' ? 'col-md-12' : 'col-md-6' }}">
                                        <label for="field_{{ $field->field_name }}" class="form-label fw-semibold fs-7 mb-1">
                                            {{ $field->label }}
                                            @if($field->is_required) <span class="text-danger">*</span> @endif
                                        </label>

                                        @if($field->field_type === 'text')
                                            <input type="text" class="form-control" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" {{ $field->is_required ? 'required' : '' }}>

                                        @elseif($field->field_type === 'number')
                                            <input type="number" step="any" class="form-control" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" {{ $field->is_required ? 'required' : '' }}>

                                        @elseif($field->field_type === 'select')
                                            <select class="form-select" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" {{ $field->is_required ? 'required' : '' }}>
                                                <option value="">-- Select {{ $field->label }} --</option>
                                                @if(is_array($field->options))
                                                    @foreach($field->options as $opt)
                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                    @endforeach
                                                @endif
                                            </select>

                                        @elseif($field->field_type === 'datetime')
                                            <input type="datetime-local" class="form-control" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="{{ date('Y-m-d\TH:i') }}" {{ $field->is_required ? 'required' : '' }}>

                                        @elseif($field->field_type === 'checkbox')
                                            <div class="form-check form-switch pt-1">
                                                <input class="form-check-input" type="checkbox" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="1">
                                                <label class="form-check-label fw-semibold fs-7" for="field_{{ $field->field_name }}">Confirmed / Passed</label>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning mb-0 fs-7">No active custom fields configured. You can still save weight and plate details.</div>
                        @endif
                    </div>
                </div>

                <!-- Submit Action Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-printer-fill fs-5"></i> Record Transaction & Generate Ticket
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let currentScaleWeight = 28500;
        let isScaleStable = true;

        const mockPlates = ['B 1234 ABC', 'B 5678 DEF', 'B 9012 GHI', 'B 7788 XYZ', 'B 4321 CDE', 'B 9900 PQR'];

        $('#plateNumberInput').on('input', function() {
            const plateVal = $(this).val().toUpperCase();
            $('#lprVisualBadge').text(plateVal || 'NO PLATE');
        });

        $('#simLprDetectionBtn').on('click', function() {
            const randomPlate = mockPlates[Math.floor(Math.random() * mockPlates.length)];
            $('#plateNumberInput').val(randomPlate).trigger('input');
        });

        function updateScaleDisplay() {
            const formatted = new Intl.NumberFormat().format(currentScaleWeight) + ' KG';
            $('#liveWeightDisplay').text(formatted);
        }

        $('.sim-weight-btn').on('click', function() {
            currentScaleWeight = parseFloat($(this).data('weight'));
            updateScaleDisplay();
        });

        $('#toggleStabilityBtn').on('click', function() {
            isScaleStable = !isScaleStable;
            const badge = $('#scaleStabilityBadge');
            if (isScaleStable) {
                badge.removeClass('bg-warning text-dark').addClass('bg-success').text('STABLE');
            } else {
                badge.removeClass('bg-success').addClass('bg-warning text-dark').text('UNSTABLE');
            }
        });

        $('#captureGrossBtn').on('click', function() {
            $('#grossWeightInput').val(currentScaleWeight.toFixed(2)).trigger('input');
        });

        $('#captureTareBtn').on('click', function() {
            $('#tareWeightInput').val(currentScaleWeight.toFixed(2)).trigger('input');
        });

        function calculateNetWeight() {
            const gross = parseFloat($('#grossWeightInput').val()) || 0;
            const tare = parseFloat($('#tareWeightInput').val()) || 0;
            const net = Math.max(0, gross - tare);

            $('#netWeightInput').val(net.toFixed(2));
        }

        $('#grossWeightInput, #tareWeightInput').on('input keyup change', function() {
            calculateNetWeight();
        });

        calculateNetWeight();
    });
</script>
@endpush
