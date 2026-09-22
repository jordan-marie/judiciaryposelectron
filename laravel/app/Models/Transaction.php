<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'initial_weight',
        'final_weight',
        'net_weight',
        'license_plate',
        'status',
        'created_by',
    ];

    protected $casts = [
        'initial_weight' => 'float',
        'final_weight' => 'float',
        'net_weight' => 'float',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fieldValues()
    {
        return $this->hasMany(TransactionFieldValue::class);
    }
}
