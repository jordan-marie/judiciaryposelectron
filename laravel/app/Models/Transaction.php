<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meta()
    {
        return $this->hasMany(TransactionMeta::class);
    }

    public function getMetaValue($key, $default = null)
    {
        $item = $this->meta->where('field_name', $key)->first();
        return $item ? $item->field_value : $default;
    }
}
