<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    /**
     * @var Department
     */
    protected $model;
    
    /**
     * Constructor
     * 
     * @param Department $department
     */
    public function __construct(Department $department)
    {
        $this->model = $department;
    }
    
    /**
     * Obtener todos los departamentos
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        try {
            return $this->model->all();
        } catch (\Exception $e) {
            Log::error('Error al obtener todos los departamentos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos paginados
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->model->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Error al obtener departamentos paginados: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener un departamento por su ID
     *
     * @param int $id
     * @return Department|null
     */
    public function findById(int $id): ?Department
    {
        try {
            return $this->model->find($id);
        } catch (\Exception $e) {
            Log::error("Error al buscar el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear un nuevo departamento
     *
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error('Error al crear un departamento: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Actualizar un departamento existente
     *
     * @param int $id
     * @param array $data
     * @return Department|null
     */
    public function update(int $id, array $data): ?Department
    {
        try {
            $department = $this->findById($id);
            
            if (!$department) {
                return null;
            }
            
            $department->update($data);
            return $department->fresh();
        } catch (\Exception $e) {
            Log::error("Error al actualizar el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Eliminar un departamento
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $department = $this->findById($id);
            
            if (!$department) {
                return false;
            }
            
            return $department->delete();
        } catch (\Exception $e) {
            Log::error("Error al eliminar el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos activos
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        try {
            return $this->model->where('is_active', true)->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener departamentos activos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos con conteo de usuarios
     *
     * @return Collection
     */
    public function getWithUsersCount(): Collection
    {
        try {
            return $this->model->withCount('users')->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener departamentos con conteo de usuarios: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos con conteo de tickets
     *
     * @return Collection
     */
    public function getWithTicketsCount(): Collection
    {
        try {
            return $this->model->withCount('tickets')->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener departamentos con conteo de tickets: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cambiar el estado de activación de un departamento
     *
     * @param int $id
     * @param bool $status
     * @return bool
     */
    public function toggleActive(int $id, bool $status): bool
    {
        try {
            $department = $this->findById($id);
            
            if (!$department) {
                return false;
            }
            
            return $department->update(['is_active' => $status]);
        } catch (\Exception $e) {
            Log::error("Error al cambiar el estado del departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cambiar el estado de activación de múltiples departamentos
     *
     * @param array $ids
     * @param bool $status
     * @return bool
     */
    public function bulkToggleActive(array $ids, bool $status): bool
    {
        try {
            return $this->model->whereIn('id', $ids)->update(['is_active' => $status]);
        } catch (\Exception $e) {
            Log::error('Error al cambiar el estado de múltiples departamentos: ' . $e->getMessage());
            throw $e;
        }
    }
}
