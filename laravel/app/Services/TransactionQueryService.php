<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionQueryService
{
    public function query(Request $request)
    {
        $query = Transaction::with(['creator', 'fieldValues.formField']);

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Filter by License Plate (fuzzy match)
        if ($request->filled('license_plate')) {
            $query->where('license_plate', 'like', '%' . $request->input('license_plate') . '%');
        }

        // Filter by Operator
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->input('created_by'));
        }

        // Filter by Status (Inbound = PENDING, Outbound/Completed = COMPLETED)
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'Inbound') {
                $query->where('status', 'PENDING');
            } elseif ($status === 'Outbound' || $status === 'Completed') {
                $query->where('status', 'COMPLETED');
            } else {
                $query->where('status', $status);
            }
        }

        // Quick search term (ticket or plate)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('license_plate', 'like', "%{$q}%");
            });
        }

        // Filter by Dynamic Form Fields
        if ($request->filled('dynamic_fields') && is_array($request->input('dynamic_fields'))) {
            foreach ($request->input('dynamic_fields') as $fieldId => $val) {
                if (!empty($val)) {
                    $query->whereHas('fieldValues', function ($sub) use ($fieldId, $val) {
                        $sub->where('form_field_id', $fieldId)
                            ->where('value', 'like', '%' . $val . '%');
                    });
                }
            }
        }

        return $query->latest();
    }
}
