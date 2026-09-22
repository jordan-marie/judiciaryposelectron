<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function index()
    {
        $forms = Form::with('fields')->get();
        return view('forms.index', compact('forms'));
    }

    public function createField(Request $request, Form $form)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'field_type' => 'required|in:text,select,number',
            'is_required' => 'nullable|boolean',
            'options' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $options = null;
        if ($validated['field_type'] === 'select' && !empty($validated['options'])) {
            $options = array_map('trim', explode(',', $validated['options']));
        }

        FormField::create([
            'form_id' => $form->id,
            'field_name' => \Str::slug($validated['field_name'], '_'),
            'label' => $validated['label'],
            'field_type' => $validated['field_type'],
            'is_required' => $request->boolean('is_required'),
            'options_json' => $options,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Form field added successfully.');
    }

    public function deleteField(FormField $field)
    {
        $field->delete();
        return redirect()->back()->with('success', 'Form field deleted successfully.');
    }
}
