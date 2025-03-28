<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Constantes para las relaciones y columnas clave
     */
    public const CREATOR_COLUMN = 'user_id';
    public const ASSIGNEE_COLUMN = 'assigned_to';
    public const DEPARTMENT_FOREIGN_KEY = 'department_id';

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene los usuarios del departamento
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, self::DEPARTMENT_FOREIGN_KEY);
    }

    /**
     * Obtiene los tickets creados por usuarios de este departamento
     */
    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ticket::class,
            User::class,
            self::DEPARTMENT_FOREIGN_KEY, // Llave foránea en users
            self::CREATOR_COLUMN, // Llave foránea en tickets que referencia al creador
            'id', // Llave local en departments
            'id' // Llave local en users
        );
    }

    /**
     * Obtiene los tickets asignados a usuarios de este departamento
     */
    public function assignedTickets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ticket::class,
            User::class,
            self::DEPARTMENT_FOREIGN_KEY, // Llave foránea en users
            self::ASSIGNEE_COLUMN, // Llave foránea en tickets
            'id', // Llave local en departments
            'id' // Llave local en users
        );
    }

    /**
     * Scope para filtrar departamentos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar departamentos por rol de usuario
     */
    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['admin', 'supervisor'])) {
            return $query;
        }

        return $query->where('id', $user->department_id);
    }
}