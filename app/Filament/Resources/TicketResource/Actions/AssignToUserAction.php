<?php

namespace App\Filament\Resources\TicketResource\Actions;

use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Services\TicketAssignmentService;

class AssignToUserAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Asignar a Usuario')
            ->icon('heroicon-o-user')
            ->color('primary')
            ->form(function (Model $record) {
                $departmentId = $record->department_id;
                $userQuery = User::query();

                if ($departmentId) {
                    $userQuery->where('department_id', $departmentId);
                }

                return [
                    Select::make('user_id')
                        ->label('Usuario')
                        ->options($userQuery->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ];
            })
            ->action(function (array $data, Model $record): void {
                try {
                    $user = User::findOrFail($data['user_id']);
                    $service = app(TicketAssignmentService::class);
                    $service->assignToUser($record, $user);

                    Notification::make()
                        ->title('Ticket asignado correctamente')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al asignar ticket')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(function (Model $record) {
                $user = auth()->guard('web')->user();
                return $user->hasPermissionTo('assign_ticket') ||
                    ($record->department_id === $user->department_id &&
                        $user->hasRole('department_admin'));
            });
    }

    /**
     * Crea una nueva instancia de la acción.
     *
     * @param  string|null  $name
     * @return static
     */
    public static function make(string|null $name = null): static
    {
        return parent::make($name ?? 'Asignar_a_Usuario');
    }
}
