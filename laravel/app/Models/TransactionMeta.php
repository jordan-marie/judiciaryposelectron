<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMeta extends Model
{
    use HasFactory;

    protected $table = 'transaction_meta';

    protected $fillable = [
        'transaction_id',
        'field_name',
        'field_value',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
