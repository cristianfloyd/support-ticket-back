<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view_any_department')) {
            return true;
        }

        // Los supervisores solo pueden ver departamentos
        if ($user->hasRole('Supervisor')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Department $department): bool
    {
        // Administradores pueden ver cualquier departamento
        if ($user->can('view_department')) {
            return true;
        }

        // Supervisores solo pueden ver su propio departamento
        if ($user->hasRole('Supervisor')) {
            return $user->department_id === $department->id;
        }

        // Usuarios regulares solo pueden ver su propio departamento
        return $user->department_id === $department->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_department');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->can('update_department')) {
            return true;
        }

        // Supervisores pueden actualizar su propio departamento
        if ($user->hasRole('Supervisor')) {
            return $user->department_id === $department->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->can('delete_department');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_department');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return $user->can('force_delete_department');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_department');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Department $department): bool
    {
        return $user->can('restore_department');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_department');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Department $department): bool
    {
        return $user->can('replicate_department');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_department');
    }
}
