<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Transaction;
use App\Models\TransactionFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeForm = Form::where('is_active', true)->with('fields')->first();
        $pendingTransactions = Transaction::where('status', 'PENDING')->latest()->get();

        $selectedPending = null;
        if ($request->has('outbound_id')) {
            $selectedPending = Transaction::with('fieldValues')->find($request->input('outbound_id'));
        }

        return view('dashboard.index', compact('activeForm', 'pendingTransactions', 'selectedPending'));
    }

    public function storeInbound(Request $request)
    {
        $request->validate([
            'license_plate' => 'required|string|max:50',
            'initial_weight' => 'required|numeric|min:0',
        ]);

        $ticketNumber = 'WB-' . date('Ymd') . '-' . strtoupper(\Str::random(5));

        $transaction = Transaction::create([
            'ticket_number' => $ticketNumber,
            'initial_weight' => $request->input('initial_weight'),
            'license_plate' => strtoupper(trim($request->input('license_plate'))),
            'status' => 'PENDING',
            'created_by' => Auth::id(),
        ]);

        $activeForm = Form::where('is_active', true)->with('fields')->first();
        if ($activeForm && $request->has('fields')) {
            foreach ($request->input('fields') as $fieldId => $val) {
                if ($val !== null) {
                    TransactionFieldValue::create([
                        'transaction_id' => $transaction->id,
                        'form_field_id' => $fieldId,
                        'value' => is_array($val) ? json_encode($val) : (string) $val,
                    ]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Inbound Weighment recorded successfully! Ticket: ' . $ticketNumber);
    }

    public function storeOutbound(Request $request, Transaction $transaction)
    {
        $request->validate([
            'final_weight' => 'required|numeric|min:0',
        ]);

        $finalWeight = (float) $request->input('final_weight');
        $initialWeight = (float) $transaction->initial_weight;
        $netWeight = abs($initialWeight - $finalWeight);

        $transaction->update([
            'final_weight' => $finalWeight,
            'net_weight' => $netWeight,
            'status' => 'COMPLETED',
        ]);

        return redirect()->route('dashboard')->with('success', 'Outbound Weighment completed! Net Weight: ' . number_format($netWeight, 2) . ' kg');
    }
}
