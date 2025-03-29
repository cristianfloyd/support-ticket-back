<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProviderResource;

class CreateProvider extends CreateRecord
{
    protected static string $resource = ProviderResource::class;
}
