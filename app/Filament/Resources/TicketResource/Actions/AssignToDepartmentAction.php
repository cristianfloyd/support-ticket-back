<?php

namespace App\Filament\Resources\TicketResource\Actions;

use App\Models\Department;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Services\TicketAssignmentService;

class AssignToDepartmentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Asignar a Departamento')
            ->icon('heroicon-o-building-office')
            ->color('success')
            ->form([
                Select::make('department_id')
                    ->label('Departamento')
                    ->options(Department::active()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ])
            ->action(function (array $data, Model $record): void {
                try {
                    $department = Department::findOrFail($data['department_id']);
                    $service = app(TicketAssignmentService::class);
                    $service->assignToDepartment($record, $department);

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
            ->visible(fn (Model $record) => auth()->guard('web')->user()->hasPermissionTo('assign_ticket'));
    }

    /**
     * Crea una nueva instancia de la acción.
     *
     * @param  string|null  $name
     * @return static
     */
    public static function make(string|null $name = null): static
    {
        return parent::make($name ?? 'Asignar_a_Departamento');
    }
}
