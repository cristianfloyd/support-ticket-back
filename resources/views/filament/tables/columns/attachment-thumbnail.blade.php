@php
    $media = $getRecord()->getFile();
@endphp

@if ($media && str_contains($media->mime_type, 'image'))
    <img 
        src="{{ $media->getUrl('thumb') }}" 
        alt="{{ $media->name }}" 
        style="max-width: 100px; max-height: 100px; object-fit: contain;"
    >
@else
    <span>-</span>
@endif
