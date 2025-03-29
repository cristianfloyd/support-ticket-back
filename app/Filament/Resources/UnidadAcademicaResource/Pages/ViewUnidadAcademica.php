<?php

namespace App\Filament\Resources\UnidadAcademicaResource\Pages;

use App\Filament\Resources\UnidadAcademicaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUnidadAcademica extends ViewRecord
{
    protected static string $resource = UnidadAcademicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
