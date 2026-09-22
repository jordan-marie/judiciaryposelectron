<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'form_field_id',
        'value',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function formField()
    {
        return $this->belongsTo(FormField::class);
    }
}
