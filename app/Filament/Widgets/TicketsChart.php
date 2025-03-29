<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;

class TicketsChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets por Estado';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $tickets = Ticket::with('status')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy('status.name')
            ->map(fn ($tickets) => $tickets->count())
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets por Estado',
                    'data' => array_values($tickets),
                    'backgroundColor' => [
                        '#f97316', // Pendiente (naranja)
                        '#22c55e', // Resuelto (verde)
                        '#3b82f6', // En Proceso (azul)
                        '#ef4444', // Cancelado (rojo)
                    ],
                ],
            ],
            'labels' => array_keys($tickets),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
