<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadAcademica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unidades_academicas';
    
    protected $fillable = ['name', 'code', 'description'];

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}