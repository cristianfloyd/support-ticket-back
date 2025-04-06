<?php

namespace App\Filament\Resources\DepartmentResource\Widgets;

use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DepartmentStatsOverview extends BaseWidget
{
    /**
     * @var DepartmentService
     */
    protected $departmentService;

    public function mount(): void
    {
        $this->departmentService = app(DepartmentService::class);
    }

    protected function getStats(): array
    {
        try {
            $statistics = $this->departmentService->getDepartmentStatistics();
            
            return [
                Stat::make('Total Departamentos', $statistics['total'])
                    ->description('Número total de departamentos')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('primary'),
                
                Stat::make('Departamentos Activos', $statistics['active'])
                    ->description('Departamentos en estado activo')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
                
                Stat::make('Departamentos Inactivos', $statistics['inactive'])
                    ->description('Departamentos en estado inactivo')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            Log::error('Error al cargar estadísticas de departamentos: ' . $e->getMessage());
            
            return [
                Stat::make('Error', 'No se pudieron cargar las estadísticas')
                    ->description('Intente recargar la página')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
