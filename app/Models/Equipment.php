<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipments';
    
    protected $fillable = [
        'name',
        'serial_number',
        'proveedor_id',
        'specifications',
        'purchase_date',
        'warranty_expiration'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiration' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}