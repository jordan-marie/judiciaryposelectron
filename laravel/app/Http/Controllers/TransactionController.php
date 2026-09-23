<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Form;
use App\Models\TransactionMeta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $forms = Form::where('is_active', true)->with('fields')->get();

        $query = Transaction::with(['form', 'creator', 'meta'])->latest();

        // Filter by Form ID
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Plate Number
        if ($request->filled('plate_number')) {
            $query->where('plate_number', 'like', '%' . $request->plate_number . '%');
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Global search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhereHas('meta', function ($metaQ) use ($search) {
                      $metaQ->where('field_value', 'like', "%{$search}%");
                  });
            });
        }

        // Dynamic Meta Field Filter
        if ($request->filled('meta_field') && $request->filled('meta_value')) {
            $fieldName = $request->meta_field;
            $fieldVal = $request->meta_value;
            $query->whereHas('meta', function ($q) use ($fieldName, $fieldVal) {
                $q->where('field_name', $fieldName)
                  ->where('field_value', 'like', "%{$fieldVal}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions', 'forms'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['form.fields', 'creator', 'meta']);
        return view('transactions.show', compact('transaction'));
    }

    public function printTicket(Transaction $transaction)
    {
        $transaction->load(['form.fields', 'creator', 'meta']);
        return view('transactions.print', compact('transaction'));
    }

    public function exportCsv(Request $request)
    {
        $query = Transaction::with(['form', 'creator', 'meta'])->latest();

        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plate_number')) {
            $query->where('plate_number', 'like', '%' . $request->plate_number . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        $response = new StreamedResponse(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Transaction Code',
                'Form Name',
                'Status',
                'Plate Number',
                'Gross Weight (KG)',
                'Tare Weight (KG)',
                'Net Weight (KG)',
                'Operator',
                'Date & Time'
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->transaction_code,
                    $tx->form ? $tx->form->name : 'N/A',
                    strtoupper($tx->status),
                    $tx->plate_number ?? 'N/A',
                    $tx->gross_weight,
                    $tx->tare_weight,
                    $tx->net_weight,
                    $tx->creator ? $tx->creator->name : 'N/A',
                    $tx->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="transactions_' . date('Ymd_His') . '.csv"');

        return $response;
    }
}
