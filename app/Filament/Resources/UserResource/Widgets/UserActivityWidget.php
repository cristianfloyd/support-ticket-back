<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Carbon\Carbon;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class UserActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Actividad del Usuario';

    protected static ?string $pollingInterval = null;

    // Para mostrar solo en la página de vista de un usuario específico
    public ?Model $record = null;

    protected function getData(): array
    {
        if (!$this->record) {
            return [];
        }

        // Obtener los últimos 30 días
        $dates = collect(range(0, 29))
            ->map(fn ($days) => Carbon::now()->subDays($days)->format('Y-m-d'))
            ->reverse();

        // Tickets creados por día
        $ticketsCreated = Ticket::where('created_by', $this->record->id)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->get()
            ->groupBy(fn ($ticket) => Carbon::parse($ticket->created_at)->format('Y-m-d'));

        // Tickets resueltos por día
        $ticketsResolved = Ticket::where('assigned_to', $this->record->id)
            ->whereHas('status', fn ($query) => $query->where('name', 'Resuelto'))
            ->whereDate('updated_at', '>=', Carbon::now()->subDays(30))
            ->get()
            ->groupBy(fn ($ticket) => Carbon::parse($ticket->updated_at)->format('Y-m-d'));

        // Preparar datos para el gráfico
        $createdData = $dates->map(fn ($date) => $ticketsCreated->get($date, collect())->count())->toArray();
        $resolvedData = $dates->map(fn ($date) => $ticketsResolved->get($date, collect())->count())->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets Creados',
                    'data' => $createdData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Tickets Resueltos',
                    'data' => $resolvedData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $dates->map(fn ($date) => Carbon::parse($date)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
