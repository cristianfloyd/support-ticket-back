<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    // ##############################   GETTERS & SETTERS   ##############################
    public function getCreatedByAttribute()
    {
        return $this->user_id;
    }

    public function setCreatedByAttribute($value)
    {
        $this->attributes['user_id'] = $value;
    }

    // Método helper para acceder a los archivos a través de attachments
    public function getFilesAttribute()
    {
        return $this->attachments->flatMap(function($attachment) {
            return $attachment->getMedia('file');
        });
    }

    // ##################################### MEDIA LIBRARY #####################################

    // Método para obtener los adjuntos (para mantener compatibilidad)
    public function getAttachmentsAttribute()
    {
        return $this->getMedia('attachments');
    }

    // #####################################    RELACIONES   #####################################

    /**
     * Obtiene el usuario que creó el ticket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function unidadAcademica(): BelongsTo
    {
        return $this->belongsTo(UnidadAcademica::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }
}
