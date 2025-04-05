@php
    $media = $getRecord()->getFile();
    $mediaUrl = App\Services\MediaService::getSecureMediaUrl($media, 'thumb');
@endphp

@if ($media && str_contains($media->mime_type, 'image') && $mediaUrl)
    <img 
        src="{{ $mediaUrl }}" 
        alt="{{ $media->name }}" 
        style="max-width: 100px; max-height: 100px; object-fit: contain;"
    >
@else
    <span>-</span>
@endif