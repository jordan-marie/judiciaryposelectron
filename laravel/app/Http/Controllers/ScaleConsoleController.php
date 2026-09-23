<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Transaction;
use App\Models\TransactionMeta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScaleConsoleController extends Controller
{
    /**
     * Display the main Scale Terminal Operator interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        // Get active forms assigned to operator's roles
        $formsQuery = Form::where('is_active', true)
            ->whereHas('roles', function ($query) use ($userRoleIds) {
                $query->whereIn('roles.id', $userRoleIds);
            });

        // Super Admin can see all active forms if desired
        if ($user->hasRole('Super Admin')) {
            $formsQuery = Form::where('is_active', true);
        }

        $availableForms = $formsQuery->with('fields')->get();

        $selectedFormId = $request->get('form_id');
        $activeForm = null;

        if ($selectedFormId) {
            $activeForm = $availableForms->firstWhere('id', $selectedFormId);
        }

        if (!$activeForm && $availableForms->count() > 0) {
            $activeForm = $availableForms->first();
        }

        return view('scale.index', compact('availableForms', 'activeForm'));
    }

    /**
     * AJAX endpoint to fetch dynamic fields for a selected form.
     */
    public function getFormFields(Form $form)
    {
        $user = Auth::user();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        // Check if user has role access
        if (!$user->hasRole('Super Admin')) {
            $hasAccess = $form->roles()->whereIn('roles.id', $userRoleIds)->exists();
            if (!$hasAccess || !$form->is_active) {
                return response()->json(['error' => 'Unauthorized or inactive form.'], 403);
            }
        }

        $form->load('fields');

        $html = view('scale.partials.dynamic_fields', compact('form'))->render();

        return response()->json([
            'success' => true,
            'form_id' => $form->id,
            'form_name' => $form->name,
            'description' => $form->description,
            'html' => $html,
            'fields' => $form->fields
        ]);
    }

    /**
     * Save a new weighbridge transaction.
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'gross_weight' => 'required|numeric|min:0',
            'tare_weight' => 'required|numeric|min:0',
            'net_weight' => 'required|numeric|min:0',
            'plate_number' => 'nullable|string|max:50',
            'status' => 'required|in:in_progress,completed,cancelled',
            'meta' => 'nullable|array',
        ]);

        $form = Form::with('fields')->findOrFail($validated['form_id']);

        // Validate dynamic required fields
        foreach ($form->fields as $field) {
            if ($field->is_required) {
                $metaVal = $request->input("meta.{$field->field_name}");
                if (is_null($metaVal) || $metaVal === '') {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'message' => "The field '{$field->label}' is required.",
                            'errors' => ["meta.{$field->field_name}" => ["The {$field->label} field is required."]]
                        ], 422);
                    }
                    return back()->withErrors(["meta.{$field->field_name}" => "The {$field->label} field is required."])->withInput();
                }
            }
        }

        $transaction = DB::transaction(function () use ($validated, $request, $form) {
            // Generate unique transaction code
            $todayStr = date('Ymd');
            $latestCount = Transaction::whereDate('created_at', now()->toDateString())->count() + 1;
            $code = 'WB-' . $todayStr . '-' . str_pad($latestCount, 4, '0', STR_PAD_LEFT);

            $tx = Transaction::create([
                'transaction_code' => $code,
                'form_id' => $form->id,
                'status' => $validated['status'],
                'gross_weight' => $validated['gross_weight'],
                'tare_weight' => $validated['tare_weight'],
                'net_weight' => $validated['net_weight'],
                'plate_number' => $validated['plate_number'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Save meta values
            if (!empty($validated['meta']) && is_array($validated['meta'])) {
                foreach ($validated['meta'] as $fieldName => $value) {
                    TransactionMeta::create([
                        'transaction_id' => $tx->id,
                        'field_name' => $fieldName,
                        'field_value' => is_array($value) ? json_encode($value) : (string)$value,
                    ]);
                }
            }

            return $tx;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction ' . $transaction->transaction_code . ' saved successfully!',
                'transaction' => $transaction->load('meta')
            ]);
        }

        return redirect()->route('transactions.show', $transaction->id)->with('success', 'Transaction saved successfully!');
    }
}
