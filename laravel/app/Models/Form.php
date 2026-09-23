<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'form_role');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order', 'asc');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
