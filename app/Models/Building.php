<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code', 'unidad_academica_id', 'description'];

    public function unidadAcademica()
    {
        return $this->belongsTo(UnidadAcademica::class);
    }

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}