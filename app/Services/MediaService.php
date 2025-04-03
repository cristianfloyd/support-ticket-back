<?php

namespace App\Services;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class MediaService
{
    /**
     * Formatea el tipo MIME para mostrar un nombre amigable
     *
     * @param string|null $mimeType
     * @return string
     */
    public static function formatMimeType(?string $mimeType): string
    {
        if (!$mimeType) {
            return 'Desconocido';
        }
        
        if (str_contains($mimeType, 'image')) {
            return 'Imagen';
        } elseif (str_contains($mimeType, 'pdf')) {
            return 'PDF';
        } elseif (str_contains($mimeType, 'word') || str_contains($mimeType, 'msword')) {
            return 'Word';
        } elseif (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            return 'Excel';
        } else {
            try {
                return explode('/', $mimeType)[1] ?? $mimeType;
            } catch (\Exception $e) {
                return $mimeType;
            }
        }
    }
    
    /**
     * Formatea el tamaño del archivo en KB
     *
     * @param int|null $size
     * @return string
     */
    public static function formatFileSize(?int $size): string
    {
        if (!is_numeric($size)) {
            return 'Desconocido';
        }
        return number_format($size / 1024, 2) . ' KB';
    }
    
    /**
     * Añade un archivo al modelo utilizando Spatie Media Library
     *
     * @param HasMedia $model El modelo que implementa HasMedia
     * @param string|array $file El archivo o archivos a añadir
     * @param string $collectionName El nombre de la colección
     * @param array $customProperties Propiedades personalizadas para el archivo
     * @return Media|null
     */
    public static function addMedia(HasMedia $model, $file, string $collectionName, array $customProperties = []): ?Media
    {
        try {
            return $model->addMedia($file)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collectionName);
        } catch (FileDoesNotExist | FileIsTooBig $e) {
            Log::error('Error al añadir archivo: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crea un registro de media a partir de datos de formulario
     *
     * @param HasMedia $model El modelo que implementa HasMedia
     * @param array $data Datos del formulario
     * @param string $collectionName El nombre de la colección
     * @return Media|null
     */
    public static function createMediaFromFormData(HasMedia $model, array $data, string $collectionName): ?Media
    {
        try {
            if (!isset($data['attachments']) || empty($data['attachments'])) {
                return null;
            }
            
            // Si estamos usando Filament con SpatieMediaLibraryFileUpload, 
            // los archivos ya estarán procesados de manera diferente
            $fileName = $data['attachments'][0] ?? null;
            
            if (!$fileName) {
                return null;
            }
            
            // Aquí utilizamos la API de Spatie para crear el registro
            $media = $model->media()->create([
                'collection_name' => $collectionName,
                'name' => $fileName,
                'file_name' => $fileName,
            ]);
            
            return $media;
        } catch (\Exception $e) {
            Log::error('Error al crear archivo: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtiene la URL de un archivo de media
     *
     * @param Media|null $media
     * @param string|null $conversion
     * @return string|null
     */
    public static function getMediaUrl(?Media $media, ?string $conversion = null): ?string
    {
        if (!$media) {
            return null;
        }
        
        try {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        } catch (\Exception $e) {
            Log::error('Error al obtener URL del archivo: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica si un archivo es una imagen
     *
     * @param Media|null $media
     * @return bool
     */
    public static function isImage(?Media $media): bool
    {
        if (!$media || !isset($media->mime_type)) {
            return false;
        }
        
        return str_contains($media->mime_type, 'image');
    }
    
    /**
     * Elimina un archivo de media
     *
     * @param Media|null $media
     * @return bool
     */
    public static function deleteMedia(?Media $media): bool
    {
        if (!$media) {
            return false;
        }
        
        try {
            $media->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo: ' . $e->getMessage());
            return false;
        }
    }
}
