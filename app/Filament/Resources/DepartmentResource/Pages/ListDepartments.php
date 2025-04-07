<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use Filament\Actions;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DepartmentResource;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    /**
     * Servicio de departamentos
     * 
     * @var DepartmentService
     */
    protected DepartmentService $departmentService;

    /**
     * Método de inicialización del componente Livewire
     * 
     * @param DepartmentService $departmentService Servicio inyectado automáticamente
     * @return void
     */
    public function mount(): void
    {
        $this->departmentService = app(DepartmentService::class);
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Departamento'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DepartmentResource\Widgets\DepartmentStatsOverview::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
