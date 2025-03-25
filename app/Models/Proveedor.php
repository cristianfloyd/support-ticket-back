<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}