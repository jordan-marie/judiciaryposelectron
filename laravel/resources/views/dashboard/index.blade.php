<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Weighbridge Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .weight-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 3.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            background-color: #111;
            color: #00ff66;
            border-radius: 8px;
            padding: 15px 25px;
            text-align: right;
            box-shadow: inset 0 0 10px rgba(0,255,102,0.5);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .cam-preview {
            width: 100%;
            height: 220px;
            background-color: #222;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            position: relative;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">⚖️ Weighbridge System</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link active" href="{{ route('dashboard') }}">Live Dashboard</a>
            <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
            @can('manage form builder')
                <a class="nav-link" href="{{ route('forms.index') }}">Form Builder</a>
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

<div class="container-fluid px-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Side: Live Hardware & Camera Controls -->
        <div class="col-lg-5">
            <!-- Serial Port Weight Display -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">📡 Live COM Weight Indicator</span>
                    <span id="comStatusBadge" class="badge status-badge bg-warning text-dark">DISCONNECTED</span>
                </div>
                <div class="card-body">
                    <div id="liveWeightDisplay" class="weight-display mb-3">0.00 kg</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Auto-syncing with COM serial port...</small>
                        @can('override weight')
                            <button id="btnManualOverride" class="btn btn-outline-warning btn-sm">
                                ⚙️ Manual Weight Override
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- AI / Camera Plate Recognition -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">📹 Camera & LPR Plate Detection</span>
                    <span id="lprStatusBadge" class="badge status-badge bg-secondary">STANDBY</span>
                </div>
                <div class="card-body">
                    <div class="cam-preview mb-3" id="camContainer">
                        <video id="webcam" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; display: none;"></video>
                        <canvas id="camCanvas" style="display: none;"></canvas>
                        <div id="camPlaceholder" class="text-center">
                            <p class="mb-1">📷 Camera Stream Offline</p>
                            <small class="text-muted">Click Scan Plate to initialize camera capture</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btnScanPlate" type="button" class="btn btn-primary w-100 fw-bold">
                            🔍 Scan License Plate
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inbound Vehicles Awaiting 2nd Weighment -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    🚚 Inbound Vehicles Awaiting 2nd Weighment ({{ $pendingTransactions->count() }})
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pendingTransactions as $p)
                            <a href="{{ route('dashboard', ['outbound_id' => $p->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ ($selectedPending && $selectedPending->id == $p->id) ? 'active' : '' }}">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $p->ticket_number }}</h6>
                                    <small>Plate: {{ $p->license_plate }} | Initial Wt: {{ number_format($p->initial_weight, 2) }} kg</small>
                                </div>
                                <span class="badge bg-light text-dark fw-bold">Outbound ➔</span>
                            </a>
                        @empty
                            <div class="p-3 text-center text-muted">No inbound pending vehicles.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Transaction Entry Form (Inbound or Outbound) -->
        <div class="col-lg-7">
            @if($selectedPending)
                <!-- Outbound / 2nd Weighment Form -->
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">⚖️ Perform 2nd Weighment (Outbound)</h5>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">Switch to 1st Weighment</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transactions.outbound', $selectedPending->id) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ticket Number</label>
                                    <input type="text" class="form-control" value="{{ $selectedPending->ticket_number }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">License Plate</label>
                                    <input type="text" class="form-control" value="{{ $selectedPending->license_plate }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Initial Weight (kg)</label>
                                    <input type="text" id="outboundInitialWeight" class="form-control bg-light" value="{{ $selectedPending->initial_weight }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Final Weight (kg)</label>
                                    <input type="number" step="0.01" id="outboundFinalWeight" name="final_weight" class="form-control form-control-lg fw-bold border-success" required>
                                </div>
                                <div class="col-md-12">
                                    <div class="alert alert-info text-center my-2 fs-5">
                                        Calculated Net Weight: <strong id="calculatedNetWeight">0.00</strong> kg
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                                        ✅ Complete Outbound Transaction
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- Inbound / 1st Weighment Form -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        📝 Perform 1st Weighment (Inbound)
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transactions.inbound') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">License Plate Number</label>
                                    <div class="input-group">
                                        <input type="text" id="inboundLicensePlate" name="license_plate" class="form-control form-control-lg text-uppercase fw-bold" placeholder="e.g. ABC-1234" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Initial Weight (kg)</label>
                                    <input type="number" step="0.01" id="inboundInitialWeight" name="initial_weight" class="form-control form-control-lg fw-bold" placeholder="0.00" required>
                                </div>

                                <!-- Dynamic Custom Form Fields -->
                                @if($activeForm && $activeForm->fields->count() > 0)
                                    <div class="col-12"><hr class="my-2"><h6 class="text-primary fw-bold">Custom Transaction Fields</h6></div>
                                    @foreach($activeForm->fields as $field)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                {{ $field->label }}
                                                @if($field->is_required) <span class="text-danger">*</span> @endif
                                            </label>
                                            @if($field->field_type === 'select')
                                                <select name="fields[{{ $field->id }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">-- Select {{ $field->label }} --</option>
                                                    @if($field->options_json)
                                                        @foreach($field->options_json as $opt)
                                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @elseif($field->field_type === 'number')
                                                <input type="number" step="any" name="fields[{{ $field->id }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                            @else
                                                <input type="text" name="fields[{{ $field->id }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                                        📥 Save Inbound Transaction
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
let currentLiveWeight = 0;

// Listen to Electron IPC Bridge if available
if (window.electronAPI) {
    window.electronAPI.onWeightData((data) => {
        if (data && data.weight !== undefined) {
            currentLiveWeight = parseFloat(data.weight);
            $('#liveWeightDisplay').text(currentLiveWeight.toFixed(2) + ' kg');

            // Auto update fields if empty
            if (!$('#inboundInitialWeight').data('user-modified')) {
                $('#inboundInitialWeight').val(currentLiveWeight.toFixed(2));
            }
            if (!$('#outboundFinalWeight').data('user-modified')) {
                $('#outboundFinalWeight').val(currentLiveWeight.toFixed(2));
                calculateNet();
            }
        }
    });

    window.electronAPI.onSerialStatus((status) => {
        let badge = $('#comStatusBadge');
        badge.removeClass('bg-success bg-warning bg-danger text-dark text-white');
        if (status === 'CONNECTED') {
            badge.addClass('bg-success text-white').text('CONNECTED');
        } else if (status === 'UNSTABLE') {
            badge.addClass('bg-warning text-dark').text('UNSTABLE');
        } else {
            badge.addClass('bg-danger text-white').text('DISCONNECTED');
        }
    });
} else {
    // Web fallback simulation for development
    setInterval(() => {
        currentLiveWeight = 12500 + Math.floor(Math.random() * 50);
        $('#liveWeightDisplay').text(currentLiveWeight.toFixed(2) + ' kg');
        $('#comStatusBadge').removeClass('bg-danger bg-warning').addClass('bg-success text-white').text('CONNECTED (SIMULATED)');
    }, 2000);
}

$('#inboundInitialWeight, #outboundFinalWeight').on('input', function() {
    $(this).data('user-modified', true);
    calculateNet();
});

function calculateNet() {
    let initWt = parseFloat($('#outboundInitialWeight').val()) || 0;
    let finWt = parseFloat($('#outboundFinalWeight').val()) || 0;
    let net = Math.abs(initWt - finWt);
    $('#calculatedNetWeight').text(net.toFixed(2));
}

$('#btnScanPlate').on('click', async function() {
    $('#lprStatusBadge').removeClass('bg-secondary bg-danger bg-success').addClass('bg-warning text-dark').text('SCANNING...');

    if (window.electronAPI && window.electronAPI.scanLicensePlate) {
        let result = await window.electronAPI.scanLicensePlate();
        if (result && result.confidence >= 70) {
            $('#inboundLicensePlate').val(result.plate);
            $('#lprStatusBadge').removeClass('bg-warning').addClass('bg-success text-white').text('DETECTED: ' + result.plate);
        } else {
            $('#lprStatusBadge').removeClass('bg-warning').addClass('bg-danger text-white').text('PLATE_NOT_DETECTED');
        }
    } else {
        // Fallback simulation
        setTimeout(() => {
            let mockPlate = 'KAA-8899';
            $('#inboundLicensePlate').val(mockPlate);
            $('#lprStatusBadge').removeClass('bg-warning').addClass('bg-success text-white').text('DETECTED: ' + mockPlate);
        }, 1000);
    }
});
</script>
</body>
</html>
