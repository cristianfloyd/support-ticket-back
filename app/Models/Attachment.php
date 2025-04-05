<?php

namespace App\Models;

use Spatie\Image\Enums\Fit;
use App\Services\MediaService;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
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

    protected $appends = ['file_url', 'file_size', 'file_type', 'is_image'];

    /*
    * Boot del modelo
    * Actualiza los atributos del attachment cuando se crea
    */
    protected static function booted()
    {
        parent::booted();

        static::created(function ($attachment) {
            try {
                if ($media = $attachment->getFile()) {
                    $attachment->update([
                        'path' => $media->getPath(),
                        'mime_type' => $media->mime_type,
                        'size' => $media->size
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al actualizar attachment después de crear: ' . $e->getMessage());
            }
        });
    }



    // ############################## RELATIONS ##############################

    /**
     * Relación con el ticket al que pertenece este adjunto
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    // ############################ STATIC METHODS ############################
    /**
     * Crea un nuevo attachment a partir de un archivo subido
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $ticketId
     * @param array $attributes
     * @return Attachment
     */
    public static function createFromUploadedFile($file, $ticketId, $attributes = [])
    {
        try {
            // Crear el registro de attachment con datos básicos
            $attachment = self::create([
                'ticket_id' => $ticketId,
                'filename' => $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                ...$attributes
            ]);

            // Usar MediaService para añadir el archivo a la colección
            $media = MediaService::addMedia(
                $attachment, 
                $file, 
                'file', 
                ['ticket_id' => $ticketId]
            );

            // Si se creó el media, actualizar el attachment con la información
            if ($media) {
                $attachment->update([
                    'path' => $media->getPath(),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size
                ]);
            }

            return $attachment;
        } catch (\Exception $e) {
            Log::error('Error al crear attachment desde archivo: ' . $e->getMessage());
            throw $e;
        }
    }

    // ############################## MEDIA LIBRARY ##############################
    /**
     * Registra las colecciones de medios para este modelo
     * 
     * @return void
     */
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

    /**
     * Método auxiliar para facilitar el acceso al archivo
     * 
     * @return Media|null
     */
    public function getFile()
    {
        return $this->getFirstMedia('file');
    }

    // ############################## GETTERS ##############################
    /**
     * Obtiene la URL del archivo
     * 
     * @return string|null
     */
    public function getFileUrlAttribute()
    {
        return MediaService::getMediaUrl($this->getFile());
    }
    
    /**
     * Obtiene el tamaño del archivo
     * 
     * @return int|null
     */
    public function getFileSizeAttribute()
    {
        $file = $this->getFile();
        return $file ? $file->size : null;
    }

    /**
     * Obtiene el tipo MIME del archivo
     * 
     * @return string|null
     */
    public function getFileTypeAttribute()
    {
        $file = $this->getFile();
        return $file ? $file->mime_type : null;
    }

    /**
     * Determina si el archivo es una imagen
     * 
     * @return bool
     */
    public function getIsImageAttribute()
    {
        return MediaService::isImage($this->getFile());
    }
    
    /**
     * Obtiene el tipo de archivo formateado para mostrar
     * 
     * @return string
     */
    public function getFormattedFileTypeAttribute()
    {
        return MediaService::formatMimeType($this->file_type);
    }
    
    /**
     * Obtiene el tamaño del archivo formateado
     * 
     * @return string
     */
    public function getFormattedFileSizeAttribute()
    {
        return MediaService::formatFileSize($this->file_size);
    }
}
