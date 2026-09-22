<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Transaction;
use App\Models\TransactionMeta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'meta']);

        // 1. Global Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'LIKE', "%{$search}%")
                  ->orWhere('plate_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('meta', function($mq) use ($search) {
                      $mq->where('field_value', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 2. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. Dynamic Custom Field Query Filter
        if ($request->filled('meta_field') && $request->filled('meta_value')) {
            $metaField = $request->meta_field;
            $metaValue = $request->meta_value;
            $query->whereHas('meta', function($q) use ($metaField, $metaValue) {
                $q->where('field_name', $metaField)
                  ->where('field_value', 'LIKE', "%{$metaValue}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get all dynamic field definitions from active form for dynamic query builder dropdown
        $activeForm = Form::with('fields')->where('is_active', true)->first();
        $dynamicFields = $activeForm ? $activeForm->fields : FormField::distinct()->get(['field_name', 'label']);

        return view('transactions.index', compact('transactions', 'dynamicFields'));
    }

    public function create()
    {
        $activeForm = Form::with('fields')->where('is_active', true)->first();

        if (!$activeForm) {
            $activeForm = Form::with('fields')->latest()->first();
        }

        $nextId = Transaction::max('id') + 1;
        $transactionCode = 'WB-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('transactions.create', compact('activeForm', 'transactionCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_code' => 'required|string|unique:transactions,transaction_code',
            'gross_weight' => 'required|numeric|min:0',
            'tare_weight' => 'required|numeric|min:0',
            'net_weight' => 'required|numeric',
            'plate_number' => 'required|string|max:50',
            'status' => 'required|string|in:in_progress,completed,cancelled',
            'meta' => 'nullable|array',
        ]);

        $transaction = Transaction::create([
            'transaction_code' => $request->transaction_code,
            'status' => $request->status,
            'gross_weight' => $request->gross_weight,
            'tare_weight' => $request->tare_weight,
            'net_weight' => $request->net_weight,
            'plate_number' => strtoupper(trim($request->plate_number)),
            'created_by' => Auth::id(),
        ]);

        if ($request->has('meta') && is_array($request->meta)) {
            foreach ($request->meta as $fieldName => $fieldValue) {
                if (is_array($fieldValue)) {
                    $fieldValue = json_encode($fieldValue);
                }
                TransactionMeta::create([
                    'transaction_id' => $transaction->id,
                    'field_name' => $fieldName,
                    'field_value' => $fieldValue,
                ]);
            }
        }

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', "Transaction '{$transaction->transaction_code}' recorded successfully.");
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'meta']);
        return view('transactions.show', compact('transaction'));
    }

    public function exportCsv(Request $request)
    {
        $query = Transaction::with(['user', 'meta']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'LIKE', "%{$search}%")
                  ->orWhere('plate_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('meta', function($mq) use ($search) {
                      $mq->where('field_value', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('meta_field') && $request->filled('meta_value')) {
            $metaField = $request->meta_field;
            $metaValue = $request->meta_value;
            $query->whereHas('meta', function($q) use ($metaField, $metaValue) {
                $q->where('field_name', $metaField)
                  ->where('field_value', 'LIKE', "%{$metaValue}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'weighbridge_transactions_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($file, [
                'Transaction Code',
                'Date & Time',
                'Plate Number',
                'Status',
                'Gross Weight (kg)',
                'Tare Weight (kg)',
                'Net Weight (kg)',
                'Operator',
                'Driver Name',
                'Transporter',
                'Material Type',
                'Supplier'
            ]);

            foreach ($transactions as $tx) {
                fputcsv($file, [
                    $tx->transaction_code,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->plate_number,
                    $tx->status,
                    $tx->gross_weight,
                    $tx->tare_weight,
                    $tx->net_weight,
                    $tx->user ? $tx->user->name : 'N/A',
                    $tx->getMetaValue('driver_name', '-'),
                    $tx->getMetaValue('transporter', '-'),
                    $tx->getMetaValue('material_type', '-'),
                    $tx->getMetaValue('supplier', '-')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
