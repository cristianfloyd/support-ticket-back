<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'providers';

    protected $fillable = [
        'name',
        'ruc',
        'email',
        'phone',
        'address',
        'website',
        'description',
        'contact_name',
        'contact_phone',
        'contact_email',
        'is_active',
    ];

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}