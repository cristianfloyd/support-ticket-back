<?php

namespace App\Filament\Resources\UnidadAcademicaResource\Pages;

use App\Filament\Resources\UnidadAcademicaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnidadAcademica extends EditRecord
{
    protected static string $resource = UnidadAcademicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
