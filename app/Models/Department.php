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

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 
        'description', 
        'is_active'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene los usuarios que pertenecen a este departamento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, self::DEPARTMENT_FOREIGN_KEY);
    }

    /**
     * Obtiene los tickets asociados a este departamento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
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
     * Scope para filtrar solo departamentos activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar solo departamentos inactivos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
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