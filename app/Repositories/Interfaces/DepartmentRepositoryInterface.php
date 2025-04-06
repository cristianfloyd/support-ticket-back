<?php

namespace App\Repositories\Interfaces;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DepartmentRepositoryInterface
{
    /**
     * Obtener todos los departamentos
     *
     * @return Collection
     */
    public function getAll(): Collection;
    
    /**
     * Obtener departamentos paginados
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Obtener un departamento por su ID
     *
     * @param int $id
     * @return Department|null
     */
    public function findById(int $id): ?Department;
    
    /**
     * Crear un nuevo departamento
     *
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department;
    
    /**
     * Actualizar un departamento existente
     *
     * @param int $id
     * @param array $data
     * @return Department|null
     */
    public function update(int $id, array $data): ?Department;
    
    /**
     * Eliminar un departamento
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
    
    /**
     * Obtener departamentos activos
     *
     * @return Collection
     */
    public function getActive(): Collection;
    
    /**
     * Obtener departamentos con conteo de usuarios
     *
     * @return Collection
     */
    public function getWithUsersCount(): Collection;
    
    /**
     * Obtener departamentos con conteo de tickets
     *
     * @return Collection
     */
    public function getWithTicketsCount(): Collection;
    
    /**
     * Cambiar el estado de activación de un departamento
     *
     * @param int $id
     * @param bool $status
     * @return bool
     */
    public function toggleActive(int $id, bool $status): bool;
    
    /**
     * Cambiar el estado de activación de múltiples departamentos
     *
     * @param array $ids
     * @param bool $status
     * @return bool
     */
    public function bulkToggleActive(array $ids, bool $status): bool;
}
