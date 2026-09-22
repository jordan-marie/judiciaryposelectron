<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'field_name',
        'field_type',
        'is_required',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
