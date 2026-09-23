<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weighbridge Ticket - {{ $transaction->transaction_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            color: #000;
        }
        .ticket-box {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            border: 2px dashed #000;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .ticket-box {
                border: 1px solid #000;
                margin: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary fw-bold">Print Ticket Now</button>
</div>

<div class="ticket-box">
    <div class="text-center border-bottom pb-3 mb-3">
        <h3 class="fw-bold text-uppercase mb-1">WEIGHBRIDGE OFFICIAL TICKET</h3>
        <p class="mb-0 small">Industrial Scale Operations & Management System</p>
    </div>

    <div class="row mb-3">
        <div class="col-6">
            <strong>Ticket No:</strong> {{ $transaction->transaction_code }}<br>
            <strong>Form Type:</strong> {{ $transaction->form ? $transaction->form->name : 'N/A' }}<br>
            <strong>Plate No:</strong> {{ $transaction->plate_number ?? 'N/A' }}
        </div>
        <div class="col-6 text-end">
            <strong>Date/Time:</strong> {{ $transaction->created_at->format('Y-m-d H:i:s') }}<br>
            <strong>Operator:</strong> {{ $transaction->creator ? $transaction->creator->name : 'N/A' }}<br>
            <strong>Status:</strong> {{ strtoupper($transaction->status) }}
        </div>
    </div>

    <table class="table table-bordered text-center my-3">
        <thead class="table-light">
            <tr>
                <th>GROSS WEIGHT</th>
                <th>TARE WEIGHT</th>
                <th>NET WEIGHT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fs-5 fw-bold">{{ number_format($transaction->gross_weight, 2) }} KG</td>
                <td class="fs-5 fw-bold">{{ number_format($transaction->tare_weight, 2) }} KG</td>
                <td class="fs-4 fw-bold text-dark">{{ number_format($transaction->net_weight, 2) }} KG</td>
            </tr>
        </tbody>
    </table>

    @if($transaction->meta->count() > 0)
        <div class="border-top pt-2 mt-3">
            <h6 class="fw-bold mb-2">DYNAMIC TRANSACTION DETAILS:</h6>
            <table class="table table-sm table-borderless">
                @foreach($transaction->meta as $m)
                    <tr>
                        <td class="text-muted" style="width: 50%;">{{ Str::headline($m->field_name) }}:</td>
                        <td class="fw-bold">{{ $m->field_value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="border-top pt-3 mt-4 text-center">
        <div class="row">
            <div class="col-6 border-end">
                <br><br>
                _______________________<br>
                <small>Driver Signature</small>
            </div>
            <div class="col-6">
                <br><br>
                _______________________<br>
                <small>Scale Operator Signature</small>
            </div>
        </div>
        <p class="small text-muted mt-3 mb-0">Thank you for your business. Computer generated receipt.</p>
    </div>
</div>

</body>
</html>
