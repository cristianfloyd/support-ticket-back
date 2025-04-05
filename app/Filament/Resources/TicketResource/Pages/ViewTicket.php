<?php

namespace App\Filament\Resources\TicketResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TicketResource;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
            Actions\Action::make('generatePdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-document')
                ->url(fn () => route('tickets.pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function getContentTabLabel(): string|null
    {
        return 'Detalles';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
