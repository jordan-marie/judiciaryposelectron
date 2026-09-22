<table class="table table-hover table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>Ticket #</th>
            <th>License Plate</th>
            <th>Initial Wt (kg)</th>
            <th>Final Wt (kg)</th>
            <th>Net Wt (kg)</th>
            <th>Status</th>
            <th>Operator</th>
            <th>Date & Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $t)
            <tr class="transaction-row" data-id="{{ $t->id }}">
                <td><strong>{{ $t->ticket_number }}</strong></td>
                <td><span class="badge bg-secondary fs-6">{{ $t->license_plate }}</span></td>
                <td>{{ number_format($t->initial_weight, 2) }}</td>
                <td>{{ $t->final_weight ? number_format($t->final_weight, 2) : '-' }}</td>
                <td><strong>{{ $t->net_weight ? number_format($t->net_weight, 2) : '-' }}</strong></td>
                <td>
                    @if($t->status === 'PENDING')
                        <span class="badge bg-warning text-dark">Inbound (Pending)</span>
                    @else
                        <span class="badge bg-success">Completed</span>
                    @endif
                </td>
                <td>{{ $t->creator ? $t->creator->name : 'System' }}</td>
                <td><small>{{ $t->created_at->format('Y-m-d H:i') }}</small></td>
                <td>
                    @if($t->status === 'PENDING')
                        <a href="{{ route('dashboard', ['outbound_id' => $t->id]) }}" class="btn btn-primary btn-sm fw-semibold">
                            ⚖️ Second Weighment
                        </a>
                    @else
                        <button class="btn btn-outline-secondary btn-sm" disabled>Completed</button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">No transaction records found matching the criteria.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-end mt-3">
    {{ $transactions->links() }}
</div>
