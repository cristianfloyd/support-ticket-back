<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}