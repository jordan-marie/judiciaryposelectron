<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'form_id',
        'status',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'plate_number',
        'created_by',
    ];

    protected $casts = [
        'gross_weight' => 'float',
        'tare_weight' => 'float',
        'net_weight' => 'float',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meta()
    {
        return $this->hasMany(TransactionMeta::class);
    }

    public function getMetaMapAttribute()
    {
        return $this->meta->pluck('field_value', 'field_name')->toArray();
    }
}
