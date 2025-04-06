<?php

namespace App\Filament\Resources\DepartmentResource\Widgets;

use Illuminate\View\View;
use App\Models\Department;
use Filament\Widgets\Widget;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;

class DepartmentTicketsOverview extends Widget
{
    protected static string $view = 'filament.resources.department-resource.widgets.department-tickets-overview';
    
    /**
     * @var DepartmentService
     */
    protected $departmentService;
    
    /**
     * @var int|null
     */
    public ?int $departmentId = null;
    
    /**
     * @var array
     */
    public array $ticketsData = [];
    
    public function mount(?int $departmentId = null): void
    {
        $this->departmentId = $departmentId;
        $this->departmentService = app(DepartmentService::class);
        $this->loadTicketsData();
    }
    
    /**
     * Carga los datos de tickets para el departamento
     */
    protected function loadTicketsData(): void
    {
        try {
            if ($this->departmentId) {
                $department = $this->departmentService->getDepartmentById($this->departmentId);
                
                if ($department) {
                    // Obtener tickets por estado para este departamento
                    $tickets = $department->tickets;
                    $ticketsByStatus = $tickets->groupBy('status_id');
                    
                    $statusCounts = [];
                    foreach ($ticketsByStatus as $statusId => $ticketsInStatus) {
                        $statusName = $ticketsInStatus->first()->status->name ?? "Estado {$statusId}";
                        $statusCounts[$statusName] = $ticketsInStatus->count();
                    }
                    
                    // Obtener tickets por prioridad para este departamento
                    $ticketsByPriority = $tickets->groupBy('priority_id');
                    
                    $priorityCounts = [];
                    foreach ($ticketsByPriority as $priorityId => $ticketsInPriority) {
                        $priorityName = $ticketsInPriority->first()->priority->name ?? "Prioridad {$priorityId}";
                        $priorityCounts[$priorityName] = $ticketsInPriority->count();
                    }
                    
                    $this->ticketsData = [
                        'total' => $tickets->count(),
                        'byStatus' => $statusCounts,
                        'byPriority' => $priorityCounts,
                    ];
                }
            } else {
                // Si no hay departamento específico, mostrar datos generales
                $statistics = $this->departmentService->getDepartmentStatistics();
                $this->ticketsData = [
                    'topDepartments' => $statistics['topByTickets']->map(function ($dept) {
                        return [
                            'name' => $dept->name,
                            'count' => $dept->tickets_count,
                        ];
                    })->toArray(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error al cargar datos de tickets por departamento: ' . $e->getMessage());
            $this->ticketsData = [
                'error' => 'No se pudieron cargar los datos de tickets',
            ];
        }
    }
}
