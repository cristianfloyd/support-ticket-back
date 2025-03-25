<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Redireccionar a la lista de usuarios después de crear uno nuevo
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
