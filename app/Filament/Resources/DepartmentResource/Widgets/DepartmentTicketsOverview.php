<?php

namespace App\Filament\Resources\DepartmentResource\Widgets;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DepartmentTicketsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    public ?Model $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Tickets', $this->record->tickets()->count())
                ->description('Todos los tickets del departamento')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Tickets Abiertos', $this->record->tickets()->where('status', 'open')->count())
                ->description('Tickets que requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('Tickets Resueltos', $this->record->tickets()->where('status', 'closed')->count())
                ->description('Tickets completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Tiempo Promedio de Resolución', function() {
                $avgTime = $this->record->tickets()
                    ->whereNotNull('closed_at')
                    ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, closed_at)'));

                return $avgTime ? round($avgTime, 1) . ' horas' : 'N/A';
            })
                ->description('Tiempo promedio de resolución')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
