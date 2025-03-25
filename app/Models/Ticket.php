<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'title',
        'description',
        'status_id',
        'priority_id',
        'category_id',
        'user_id',
        'assigned_to',
        'unidad_academica_id',
        'building_id',
        'office_id',
        'equipment_id',
        'is_resolved',
        'resolved_at'
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function unidadAcademica()
    {
        return $this->belongsTo(UnidadAcademica::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notificacion::class);
    }
}