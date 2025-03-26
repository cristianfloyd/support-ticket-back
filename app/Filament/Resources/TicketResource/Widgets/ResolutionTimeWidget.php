<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use Carbon\Carbon;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class ResolutionTimeWidget extends ChartWidget
{
    protected static ?string $heading = 'Tiempos de Resolución por Categoría';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $categories = \App\Models\Category::withCount(['tickets' => function ($query) {
            $query->whereNotNull('resolved_at');
        }])->get();

        $labels = $categories->pluck('name')->toArray();
        $avgResolutionTimes = [];

        foreach ($categories as $category) {
            $resolvedTickets = Ticket::where('category_id', $category->id)
                ->whereNotNull('resolved_at')
                ->get();

            if ($resolvedTickets->isEmpty()) {
                $avgResolutionTimes[] = 0;
                continue;
            }

            $totalHours = 0;
            foreach ($resolvedTickets as $ticket) {
                $created = $ticket->created_at;
                $resolved = $ticket->resolved_at;
                $diffHours = $resolved->diffInHours($created);
                $totalHours += $diffHours;
            }

            $avgHours = $totalHours / $resolvedTickets->count();
            $avgResolutionTimes[] = round($avgHours, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tiempo Promedio (horas)',
                    'data' => $avgResolutionTimes,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
