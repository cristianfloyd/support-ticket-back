<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Equipment;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Tickets', Ticket::count())
                ->description('Tickets registrados en el sistema')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart(Ticket::query()
                    ->selectRaw('COUNT(*) as count')
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('created_at')
                    ->pluck('count')
                    ->toArray())
                ->color('primary'),

            Stat::make('Tickets Pendientes', Ticket::whereHas('status', fn($query) => $query->where('name', 'Pendiente'))->count())
                ->description('Tickets sin resolver')
                ->descriptionIcon('heroicon-m-clock')
                ->chart(Ticket::query()
                    ->whereHas('status', fn($query) => $query->where('name', 'Pendiente'))
                    ->selectRaw('COUNT(*) as count')
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('created_at')
                    ->pluck('count')
                    ->toArray())
                ->color('warning'),

            Stat::make('Equipos con Garantía Vencida', Equipment::where('warranty_expiration', '<', now())->count())
                ->description('Equipos que requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->chart(Equipment::query()
                    ->where('warranty_expiration', '<', now())
                    ->selectRaw('COUNT(*) as count')
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('created_at')
                    ->pluck('count')
                    ->toArray())
                ->color('danger'),

            Stat::make('Mantenimientos Pendientes', Equipment::where('next_maintenance', '<', now())->count())
                ->description('Equipos que necesitan mantenimiento')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->chart(Equipment::query()
                    ->where('next_maintenance', '<', now())
                    ->selectRaw('COUNT(*) as count')
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('created_at')
                    ->pluck('count')
                    ->toArray())
                ->color('warning'),
        ];
    }
}
