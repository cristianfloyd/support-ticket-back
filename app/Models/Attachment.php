<?php

namespace App\Models;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\Conversions\Manipulations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Attachment extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'ticket_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size'
    ];

    protected static function booted()
    {
        parent::booted();

        static::created(function ($attachment) {
            if ($media = $attachment->getFile()) {
                $attachment->update([
                    'path' => $media->getPath(),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size
                ]);
            }
        });
    }


    protected $appends = ['file_url', 'file_size', 'file_type', 'is_image'];

    // ############################## RELATIONS ##############################

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    // ############################ STATIC METHODS ############################
    public static function createFromUploadedFile($file, $ticketId, $attributes = [])
    {
        $attachment = self::create([
            'ticket_id' => $ticketId,
            'filename' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            ...$attributes
        ]);

        $attachment->addMedia($file)
            ->toMediaCollection('file');

        return $attachment;
    }

    // ############################## MEDIA LIBRARY ##############################
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')
            ->singleFile() // Si cada Attachment tiene un solo archivo
            ->useDisk('public');
    }

    /**
     * Registra las conversiones de medios para generar thumbnails
     * 
     * @param Media $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Registramos la conversión 'thumb' para generar miniaturas
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 100, 100)
            ->nonQueued();
    }

    // Método auxiliar para facilitar el acceso al archivo
    public function getFile()
    {
        return $this->getFirstMedia('file');
    }

    // ############################## GETTERS ##############################
    public function getFileUrlAttribute()
    {
        return $this->getFile()?->getUrl();
    }
    

    public function getFileSizeAttribute()
    {
        return $this->getFile()?->size;
    }

    public function getFileTypeAttribute()
    {
        return $this->getFile()?->mime_type;
    }

    public function getIsImageAttribute()
    {
        return $this->getFile() ? str_contains($this->getFile()->mime_type, 'image') : false;
    }
}
