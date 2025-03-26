<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TicketStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total de Tickets', Ticket::count())
                ->description('Todos los tickets en el sistema')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('gray'),

            Stat::make('Tickets Pendientes', Ticket::where('is_resolved', false)->count())
                ->description('Tickets sin resolver')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Tickets Resueltos', Ticket::where('is_resolved', true)->count())
                ->description('Tickets completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Resolución Promedio', function () {
                $resolvedTickets = Ticket::whereNotNull('resolved_at')->get();
                if ($resolvedTickets->isEmpty()) return 'N/A';

                $totalHours = 0;
                foreach ($resolvedTickets as $ticket) {
                    $created = $ticket->created_at;
                    $resolved = $ticket->resolved_at;
                    $diffHours = $resolved->diffInHours($created);
                    $totalHours += $diffHours;
                }

                $avgHours = $totalHours / $resolvedTickets->count();

                if ($avgHours < 24) {
                    return round($avgHours, 1) . ' horas';
                } else {
                    return round($avgHours / 24, 1) . ' días';
                }
            })
                ->description('Tiempo promedio de resolución')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
