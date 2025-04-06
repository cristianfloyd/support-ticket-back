<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use Filament\Actions;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\DepartmentResource;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    /**
     * @var DepartmentService
     */
    protected $departmentService;

    public function mount($record): void
    {
        $this->departmentService = app(DepartmentService::class);
        parent::mount($record);
    }

    /**
     * Método para actualizar el departamento usando el servicio
     *
     * @param array $data
     * @return mixed
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return $this->departmentService->updateDepartment($record->id, $data);
        } catch (\Exception $e) {
            Log::error('Error al actualizar departamento: ' . $e->getMessage());
            $this->halt();
            $this->notify('danger', 'Error al actualizar el departamento: ' . $e->getMessage());
            throw $e;
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Departamento')
                ->before(function (Actions\DeleteAction $action) {
                    // Verificamos si el departamento tiene usuarios o tickets antes de eliminar
                    if ($this->departmentService->hasDepartmentUsers($this->record->id)) {
                        $action->cancel();
                        $this->notify('danger', 'No se puede eliminar el departamento porque tiene usuarios asignados.');
                        return;
                    }
                    
                    if ($this->departmentService->hasDepartmentTickets($this->record->id)) {
                        $action->cancel();
                        $this->notify('danger', 'No se puede eliminar el departamento porque tiene tickets asociados.');
                        return;
                    }
                }),
            
            Actions\Action::make('toggleActive')
                ->label(fn () => $this->record->is_active ? 'Desactivar' : 'Activar')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->action(function () {
                    try {
                        $newStatus = !$this->record->is_active;
                        $this->departmentService->toggleDepartmentActive($this->record->id, $newStatus);
                        $this->notify('success', $newStatus ? 'Departamento activado correctamente' : 'Departamento desactivado correctamente');
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Log::error('Error al cambiar estado del departamento: ' . $e->getMessage());
                        $this->notify('danger', 'Error al cambiar el estado del departamento');
                    }
                }),
        ];
    }
}
