<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UserStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Para mostrar solo en la página de vista de un usuario específico
    public ?Model $record = null;

    protected function getStats(): array
    {
        // Si no hay un usuario específico, mostrar estadísticas generales
        if (!$this->record) {
            return [
                Stat::make('Total de Usuarios', User::count())
                    ->description('Usuarios registrados en el sistema')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('primary'),

                Stat::make('Usuarios Activos', User::where('is_active', true)->count())
                    ->description('Usuarios con acceso al sistema')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Usuarios Inactivos', User::where('is_active', false)->count())
                    ->description('Usuarios sin acceso al sistema')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        }

        // Estadísticas específicas para un usuario
        $ticketsCreated = Ticket::where('created_by', $this->record->id)->count();
        $ticketsAssigned = Ticket::where('assigned_to', $this->record->id)->count();
        $ticketsResolved = Ticket::where('assigned_to', $this->record->id)
            ->whereHas('status', fn ($query) => $query->where('name', 'Resuelto'))
            ->count();

        $resolutionRate = $ticketsAssigned > 0
            ? round(($ticketsResolved / $ticketsAssigned) * 100)
            : 0;

        return [
            Stat::make('Tickets Creados', $ticketsCreated)
                ->description('Total de tickets creados por el usuario')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Tickets Asignados', $ticketsAssigned)
                ->description('Total de tickets asignados al usuario')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Tasa de Resolución', $resolutionRate . '%')
                ->description($ticketsResolved . ' de ' . $ticketsAssigned . ' tickets resueltos')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($resolutionRate >= 70 ? 'success' : ($resolutionRate >= 40 ? 'warning' : 'danger')),
        ];
    }
}
