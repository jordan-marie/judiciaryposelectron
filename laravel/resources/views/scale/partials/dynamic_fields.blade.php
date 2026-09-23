@php
    $values = $metaValues ?? [];
@endphp

@forelse($form->fields as $field)
    @php
        $val = $values[$field->field_name] ?? '';
    @endphp
    <div class="col-12 col-md-6 mb-3">
        <label for="field_{{ $field->field_name }}" class="form-label fw-semibold">
            {{ $field->label }}
            @if($field->is_required)
                <span class="text-danger">*</span>
            @endif
        </label>

        @if($field->field_type === 'text')
            <input type="text" class="form-control dynamic-input" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }} placeholder="Enter {{ strtolower($field->label) }}">

        @elseif($field->field_type === 'number')
            <input type="number" step="any" class="form-control dynamic-input" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }} placeholder="0.00">

        @elseif($field->field_type === 'select')
            <select class="form-select dynamic-input" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" {{ $field->is_required ? 'required' : '' }}>
                <option value="">-- Select {{ $field->label }} --</option>
                @if(is_array($field->options))
                    @foreach($field->options as $option)
                        <option value="{{ $option }}" {{ $val == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                @endif
            </select>

        @elseif($field->field_type === 'datetime')
            <input type="datetime-local" class="form-control dynamic-input" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="{{ $val ?: date('Y-m-d\TH:i') }}" {{ $field->is_required ? 'required' : '' }}>

        @elseif($field->field_type === 'checkbox')
            <div class="form-check mt-2">
                <input class="form-check-input dynamic-input" type="checkbox" id="field_{{ $field->field_name }}" name="meta[{{ $field->field_name }}]" value="1" {{ $val == '1' ? 'checked' : '' }}>
                <label class="form-check-label fw-normal" for="field_{{ $field->field_name }}">
                    Yes, confirm {{ strtolower($field->label) }}
                </label>
            </div>
        @endif
    </div>
@empty
    <div class="col-12 text-muted py-3 text-center">
        <em>No dynamic fields configured for this form.</em>
    </div>
@endforelse
