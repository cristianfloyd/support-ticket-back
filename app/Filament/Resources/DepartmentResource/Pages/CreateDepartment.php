<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use Filament\Actions;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\DepartmentResource;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    /**
     * @var DepartmentService
     */
    protected $departmentService;

    public function mount(): void
    {
        $this->departmentService = app(DepartmentService::class);
        parent::mount();
    }

    /**
     * Método para crear el departamento usando el servicio
     *
     * @param array $data
     * @return mixed
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return $this->departmentService->createDepartment($data);
        } catch (\Exception $e) {
            Log::error('Error al crear departamento: ' . $e->getMessage());
            $this->halt();
            $this->notify('danger', 'Error al crear el departamento: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
