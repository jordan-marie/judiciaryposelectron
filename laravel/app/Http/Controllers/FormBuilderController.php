<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Str;

class FormBuilderController extends Controller
{
    public function index()
    {
        $forms = Form::withCount('fields')->get();
        return view('admin.form-builder.index', compact('forms'));
    }

    public function create()
    {
        return view('admin.form-builder.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|in:text,number,select,datetime,checkbox',
            'fields.*.is_required' => 'nullable',
            'fields.*.options' => 'nullable|string',
        ]);

        if ($request->boolean('is_active')) {
            Form::query()->update(['is_active' => false]);
        }

        $form = Form::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $sortOrder = 1;
        foreach ($request->fields as $fieldData) {
            $options = null;
            if ($fieldData['field_type'] === 'select' && !empty($fieldData['options'])) {
                $options = array_values(array_filter(array_map('trim', explode(',', $fieldData['options']))));
            }

            $form->fields()->create([
                'label' => $fieldData['label'],
                'field_name' => Str::slug($fieldData['field_name'], '_'),
                'field_type' => $fieldData['field_type'],
                'is_required' => isset($fieldData['is_required']) && ($fieldData['is_required'] == '1' || $fieldData['is_required'] == 'on' || $fieldData['is_required'] === true),
                'options' => $options,
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()->route('form-builder.index')->with('success', "Form '{$form->name}' created successfully.");
    }

    public function edit(Form $form)
    {
        $form->load('fields');
        return view('admin.form-builder.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|in:text,number,select,datetime,checkbox',
            'fields.*.is_required' => 'nullable',
            'fields.*.options' => 'nullable|string',
        ]);

        if ($request->boolean('is_active')) {
            Form::where('id', '!=', $form->id)->update(['is_active' => false]);
        }

        $form->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Replace existing fields with updated set
        $form->fields()->delete();

        $sortOrder = 1;
        foreach ($request->fields as $fieldData) {
            $options = null;
            if ($fieldData['field_type'] === 'select' && !empty($fieldData['options'])) {
                if (is_array($fieldData['options'])) {
                    $options = $fieldData['options'];
                } else {
                    $options = array_values(array_filter(array_map('trim', explode(',', $fieldData['options']))));
                }
            }

            $form->fields()->create([
                'label' => $fieldData['label'],
                'field_name' => Str::slug($fieldData['field_name'], '_'),
                'field_type' => $fieldData['field_type'],
                'is_required' => isset($fieldData['is_required']) && ($fieldData['is_required'] == '1' || $fieldData['is_required'] == 'on' || $fieldData['is_required'] === true),
                'options' => $options,
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()->route('form-builder.index')->with('success', "Form '{$form->name}' updated successfully.");
    }

    public function activate(Form $form)
    {
        Form::query()->update(['is_active' => false]);
        $form->update(['is_active' => true]);

        return redirect()->route('form-builder.index')->with('success', "Form '{$form->name}' is now active.");
    }

    public function destroy(Form $form)
    {
        if ($form->is_active) {
            return back()->with('error', 'Cannot delete the active weighbridge form.');
        }

        $form->delete();
        return redirect()->route('form-builder.index')->with('success', 'Form deleted successfully.');
    }
}
