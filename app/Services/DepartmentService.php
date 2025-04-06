<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;

class DepartmentService
{
    /**
     * @var DepartmentRepositoryInterface
     */
    protected $departmentRepository;
    
    /**
     * Constructor
     * 
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }
    
    /**
     * Obtener todos los departamentos
     * 
     * @return Collection
     */
    public function getAllDepartments(): Collection
    {
        try {
            // Implementación de caché para mejorar rendimiento
            return Cache::remember('departments.all', 60 * 30, function () {
                return $this->departmentRepository->getAll();
            });
        } catch (\Exception $e) {
            Log::error('Error en el servicio al obtener todos los departamentos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos paginados
     * 
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedDepartments(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->departmentRepository->getPaginated($perPage);
        } catch (\Exception $e) {
            Log::error('Error en el servicio al obtener departamentos paginados: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener un departamento por su ID
     * 
     * @param int $id
     * @return Department|null
     */
    public function getDepartmentById(int $id): ?Department
    {
        try {
            return Cache::remember("departments.{$id}", 60 * 15, function () use ($id) {
                return $this->departmentRepository->findById($id);
            });
        } catch (\Exception $e) {
            Log::error("Error en el servicio al obtener el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear un nuevo departamento
     * 
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        try {
            $department = $this->departmentRepository->create($data);
            
            // Limpiar caché relacionada
            $this->clearDepartmentCache();
            
            return $department;
        } catch (\Exception $e) {
            Log::error('Error en el servicio al crear un departamento: ' . $e->getMessage());
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
    public function updateDepartment(int $id, array $data): ?Department
    {
        try {
            $department = $this->departmentRepository->update($id, $data);
            
            // Limpiar caché relacionada
            $this->clearDepartmentCache($id);
            
            return $department;
        } catch (\Exception $e) {
            Log::error("Error en el servicio al actualizar el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Eliminar un departamento
     * 
     * @param int $id
     * @return bool
     */
    public function deleteDepartment(int $id): bool
    {
        try {
            $result = $this->departmentRepository->delete($id);
            
            // Limpiar caché relacionada
            $this->clearDepartmentCache($id);
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error en el servicio al eliminar el departamento con ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener departamentos activos
     * 
     * @return Collection
     */
    public function getActiveDepartments(): Collection
    {
        try {
            return Cache::remember('departments.active', 60 * 15, function () {
                return $this->departmentRepository->getActive();
            });
        } catch (\Exception $e) {
            Log::error('Error en el servicio al obtener departamentos activos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener estadísticas de departamentos
     * 
     * @return array
     */
    public function getDepartmentStatistics(): array
    {
        try {
            return Cache::remember('departments.statistics', 60 * 60, function () {
                $totalDepartments = $this->departmentRepository->getAll()->count();
                $activeDepartments = $this->departmentRepository->getActive()->count();
                $departmentsWithUsers = $this->departmentRepository->getWithUsersCount();
                $departmentsWithTickets = $this->departmentRepository->getWithTicketsCount();
                
                $topDepartmentsByUsers = $departmentsWithUsers->sortByDesc('users_count')->take(5);
                $topDepartmentsByTickets = $departmentsWithTickets->sortByDesc('tickets_count')->take(5);
                
                return [
                    'total' => $totalDepartments,
                    'active' => $activeDepartments,
                    'inactive' => $totalDepartments - $activeDepartments,
                    'topByUsers' => $topDepartmentsByUsers,
                    'topByTickets' => $topDepartmentsByTickets,
                ];
                    });
                } catch (\Exception $e) {
                    Log::error('Error en el servicio al obtener estadísticas de departamentos: ' . $e->getMessage());
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
            public function toggleDepartmentActive(int $id, bool $status): bool
            {
                try {
                    $result = $this->departmentRepository->toggleActive($id, $status);
            
                    // Limpiar caché relacionada
                    $this->clearDepartmentCache($id);
            
                    return $result;
                } catch (\Exception $e) {
                    Log::error("Error en el servicio al cambiar el estado del departamento con ID {$id}: " . $e->getMessage());
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
            public function bulkToggleDepartmentsActive(array $ids, bool $status): bool
            {
                try {
                    $result = $this->departmentRepository->bulkToggleActive($ids, $status);
            
                    // Limpiar caché relacionada
                    $this->clearDepartmentCache();
            
                    return $result;
                } catch (\Exception $e) {
                    Log::error('Error en el servicio al cambiar el estado de múltiples departamentos: ' . $e->getMessage());
                    throw $e;
                }
            }
    
            /**
             * Verificar si un departamento tiene usuarios asignados
             * 
             * @param int $id
             * @return bool
             */
            public function hasDepartmentUsers(int $id): bool
            {
                try {
                    $department = $this->getDepartmentById($id);
            
                    if (!$department) {
                        return false;
                    }
            
                    return $department->users()->count() > 0;
                } catch (\Exception $e) {
                    Log::error("Error en el servicio al verificar usuarios del departamento con ID {$id}: " . $e->getMessage());
                    throw $e;
                }
            }
    
            /**
             * Verificar si un departamento tiene tickets asignados
             * 
             * @param int $id
             * @return bool
             */
            public function hasDepartmentTickets(int $id): bool
            {
                try {
                    $department = $this->getDepartmentById($id);
            
                    if (!$department) {
                        return false;
                    }
            
                    return $department->tickets()->count() > 0;
                } catch (\Exception $e) {
                    Log::error("Error en el servicio al verificar tickets del departamento con ID {$id}: " . $e->getMessage());
                    throw $e;
                }
            }
    
            /**
             * Limpiar la caché relacionada con departamentos
             * 
             * @param int|null $id
             * @return void
             */
            protected function clearDepartmentCache(?int $id = null): void
            {
                try {
                    Cache::forget('departments.all');
                    Cache::forget('departments.active');
                    Cache::forget('departments.statistics');
            
                    if ($id) {
                        Cache::forget("departments.{$id}");
                    }
                } catch (\Exception $e) {
                    Log::warning('Error al limpiar la caché de departamentos: ' . $e->getMessage());
                }
            }
}