<?php

namespace App\Filament\Resources\TicketResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\TicketResource\Widgets\TicketStatsWidget;
use App\Filament\Resources\TicketResource\Widgets\PendingTicketsWidget;
use App\Filament\Resources\TicketResource\Widgets\ResolutionTimeWidget;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TicketStatsWidget::class,
            PendingTicketsWidget::class,
            ResolutionTimeWidget::class,
        ];
    }
}
