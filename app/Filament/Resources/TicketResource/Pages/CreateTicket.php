<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    // Redireccionar a la lista de tickets después de crear uno nuevo
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
