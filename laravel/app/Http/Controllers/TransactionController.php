<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionQueryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    protected $queryService;

    public function __construct(TransactionQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    public function index(Request $request)
    {
        $query = $this->queryService->query($request);
        $transactions = $query->paginate(15)->appends($request->all());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('transactions._table', compact('transactions'))->render(),
                'pagination' => (string) $transactions->links()
            ]);
        }

        $activeForm = Form::where('is_active', true)->with('fields')->first();
        $operators = User::all();

        return view('transactions.index', compact('transactions', 'activeForm', 'operators'));
    }

    public function exportCsv(Request $request)
    {
        $transactions = $this->queryService->query($request)->get();
        $activeForm = Form::where('is_active', true)->with('fields')->first();
        $customFields = $activeForm ? $activeForm->fields : collect();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="weighbridge_transactions_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($transactions, $customFields) {
            $file = fopen('php://output', 'w');

            // Header row
            $headerRow = ['Ticket Number', 'License Plate', 'Initial Weight (kg)', 'Final Weight (kg)', 'Net Weight (kg)', 'Status', 'Operator', 'Created At'];
            foreach ($customFields as $field) {
                $headerRow[] = $field->label;
            }
            fputcsv($file, $headerRow);

            // Data rows
            foreach ($transactions as $t) {
                $row = [
                    $t->ticket_number,
                    $t->license_plate,
                    $t->initial_weight,
                    $t->final_weight,
                    $t->net_weight,
                    $t->status,
                    $t->creator ? $t->creator->name : 'N/A',
                    $t->created_at->toDateTimeString(),
                ];

                $fieldValueMap = $t->fieldValues->pluck('value', 'form_field_id');
                foreach ($customFields as $field) {
                    $row[] = $fieldValueMap[$field->id] ?? '';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
