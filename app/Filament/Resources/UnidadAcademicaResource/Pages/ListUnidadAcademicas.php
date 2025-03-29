<?php

namespace App\Filament\Resources\UnidadAcademicaResource\Pages;

use App\Filament\Resources\UnidadAcademicaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnidadAcademicas extends ListRecords
{
    protected static string $resource = UnidadAcademicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
