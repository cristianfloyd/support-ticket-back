<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Priority extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'color', 'is_active'];

    protected $casts = [
        'deleted_at' => 'datetime',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}