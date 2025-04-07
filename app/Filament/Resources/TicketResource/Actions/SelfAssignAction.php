<?php

namespace App\Filament\Resources\TicketResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Services\TicketAssignmentService;

class SelfAssignAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Auto-asignarme')
            ->icon('heroicon-o-hand-raised')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('¿Deseas auto-asignarte este ticket?')
            ->modalDescription('Serás responsable de resolver este ticket.')
            ->modalSubmitActionLabel('Sí, asignarme')
            ->action(function (Model $record): void {
                try {
                    $user = auth()->guard('web')->user();
                    $service = app(TicketAssignmentService::class);
                    $service->selfAssign($record, $user);

                    Notification::make()
                        ->title('Ticket auto-asignado correctamente')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al auto-asignar ticket')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(function (Model $record): bool {
                $user = auth()->guard('web')->user();

                // Solo visible si el ticket no está asignado a un usuario
                // o si pertenece al departamento del usuario actual
                return !$record->assigned_to &&
                       (!$record->department_id || $record->department_id === $user->department_id);
            });
    }

    /**
     * Crea una nueva instancia de la acción de auto-asignación.
     *
     * @param  string|null  $name
     * @return static
     */
    public static function make(string|null $name = null): static
    {
        return parent::make($name ?? 'Asignar_a_Mí');
    }
}
