<?php

namespace App\Filament\Resources\DepartmentResource\Widgets;

use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DepartmentStatsOverview extends BaseWidget
{
    /**
     * Servicio para obtener estadísticas de departamentos
     * 
     * @var DepartmentService
     */
    protected DepartmentService $departmentService;

    /**
     * Inicializa el widget y sus dependencias
     */
    public function mount(): void
    {
        $this->departmentService = app(DepartmentService::class);
    }

    /**
     * Define las estadísticas que se mostrarán en el widget
     * 
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        try {
            // Obtiene las estadísticas desde el servicio
            $statistics = $this->departmentService->getDepartmentStatistics();
            
            return [
                // Estadística del total de departamentos
                Stat::make('Total Departamentos', $statistics['total'])
                    ->description('Número total de departamentos')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('primary'),
                
                // Estadística de departamentos activos
                Stat::make('Departamentos Activos', $statistics['active'])
                    ->description('Departamentos en estado activo')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
                
                // Estadística de departamentos inactivos
                Stat::make('Departamentos Inactivos', $statistics['inactive'])
                    ->description('Departamentos en estado inactivo')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            // Manejo de errores con registro en log
            Log::error('Error al cargar estadísticas de departamentos: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Devuelve un mensaje de error como estadística
            return [
                Stat::make('Error', 'No se pudieron cargar las estadísticas')
                    ->description('Intente recargar la página')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
