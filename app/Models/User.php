<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'is_active',
        'profile_photo',
        'phone',
        'extension',
        'position',
        'address',
        'notification_preferences',
        'language',
        'theme',
        'two_factor_enabled',
        'account_locked',
        'failed_login_attempts',
        'last_login_at',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben convertirse.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'notification_preferences' => 'array',
        'two_factor_enabled' => 'boolean',
        'account_locked' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Obtiene el departamento al que pertenece el usuario.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Obtiene los tickets creados por el usuario.
     */
    public function ticketsCreated()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    /**
     * Obtiene los tickets asignados al usuario.
     */
    public function ticketsAssigned()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Obtiene las iniciales del nombre del usuario.
     */
    public function initials()
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return strlen($initials) > 2 ? substr($initials, 0, 2) : $initials;
    }

    /**
     * Verifica si la cuenta del usuario está bloqueada.
     */
    public function isLocked()
    {
        return $this->account_locked;
    }

    /**
     * Incrementa el contador de intentos fallidos de inicio de sesión.
     */
    public function incrementFailedLoginAttempts()
    {
        $this->failed_login_attempts += 1;

        // Bloquear la cuenta después de 5 intentos fallidos
        if ($this->failed_login_attempts >= 5) {
            $this->account_locked = true;
        }

        $this->save();
    }

    /**
     * Reinicia el contador de intentos fallidos de inicio de sesión.
     */
    public function resetFailedLoginAttempts()
    {
        $this->failed_login_attempts = 0;
        $this->account_locked = false;
        $this->last_login_at = now();
        $this->save();
    }
}
