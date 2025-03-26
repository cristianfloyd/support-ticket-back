<?php

namespace App\Filament\Resources\TicketResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TicketResource;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('resolve')
                ->label('Resolver')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn ($record) => !$record->is_resolved)
                ->action(function () {
                    $this->record->update([
                        'is_resolved' => true,
                        'resolved_at' => now(),
                    ]);
                    $this->notify('success', 'Ticket resuelto correctamente');
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    // Redireccionar a la lista de tickets después de editar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
