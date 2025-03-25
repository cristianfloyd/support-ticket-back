<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'offices';

    protected $fillable = ['name', 'code', 'building_id', 'description'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}