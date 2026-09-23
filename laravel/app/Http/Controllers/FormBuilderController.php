<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FormBuilderController extends Controller
{
    public function index()
    {
        $forms = Form::with(['roles', 'fields'])->withCount(['fields', 'transactions'])->latest()->get();
        return view('admin.form-builder.index', compact('forms'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.form-builder.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|in:text,number,select,datetime,checkbox',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.options' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $slug = Str::slug($validated['name']);
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Form::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $form = Form::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            $form->roles()->sync($validated['roles']);

            foreach ($validated['fields'] as $index => $fieldData) {
                $fieldName = Str::slug($fieldData['label'], '_');
                if (empty($fieldName)) {
                    $fieldName = 'field_' . ($index + 1);
                }

                $optionsArray = null;
                if (!empty($fieldData['options']) && $fieldData['field_type'] === 'select') {
                    $optionsArray = array_map('trim', explode(',', $fieldData['options']));
                }

                FormField::create([
                    'form_id' => $form->id,
                    'label' => $fieldData['label'],
                    'field_name' => $fieldName,
                    'field_type' => $fieldData['field_type'],
                    'is_required' => !empty($fieldData['is_required']),
                    'options' => $optionsArray,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.forms.index')->with('success', 'Form created successfully!');
    }

    public function edit(Form $form)
    {
        $form->load(['roles', 'fields']);
        $roles = Role::all();
        return view('admin.form-builder.edit', compact('form', 'roles'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|in:text,number,select,datetime,checkbox',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.options' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $form) {
            $form->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            $form->roles()->sync($validated['roles']);

            // Rebuild fields
            $form->fields()->delete();

            foreach ($validated['fields'] as $index => $fieldData) {
                $fieldName = Str::slug($fieldData['label'], '_');
                if (empty($fieldName)) {
                    $fieldName = 'field_' . ($index + 1);
                }

                $optionsArray = null;
                if (!empty($fieldData['options']) && $fieldData['field_type'] === 'select') {
                    $optionsArray = array_map('trim', explode(',', $fieldData['options']));
                }

                FormField::create([
                    'form_id' => $form->id,
                    'label' => $fieldData['label'],
                    'field_name' => $fieldName,
                    'field_type' => $fieldData['field_type'],
                    'is_required' => !empty($fieldData['is_required']),
                    'options' => $optionsArray,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.forms.index')->with('success', 'Form updated successfully!');
    }

    public function toggleStatus(Form $form)
    {
        $form->update(['is_active' => !$form->is_active]);
        return back()->with('success', 'Form status updated!');
    }

    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('admin.forms.index')->with('success', 'Form deleted successfully!');
    }
}
