<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'field_name',
        'field_type',
        'label',
        'is_required',
        'options_json',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options_json' => 'array',
        'sort_order' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function values()
    {
        return $this->hasMany(TransactionFieldValue::class);
    }
}
