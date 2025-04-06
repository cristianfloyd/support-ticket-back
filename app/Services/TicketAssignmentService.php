<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketAssignmentService
{
    /**
     * Asigna un ticket a un departamento específico
     *
     * @param Ticket $ticket
     * @param Department $department
     * @return Ticket
     */
    public function assignToDepartment(Ticket $ticket, Department $department): Ticket
    {
        try {
            DB::beginTransaction();
            
            // Actualizar el ticket con el departamento asignado
            $ticket->department_id = $department->id;
            
            // Si había un usuario asignado de otro departamento, lo quitamos
            if ($ticket->assigned_to) {
                $assignedUser = User::find($ticket->assigned_to);
                if ($assignedUser && $assignedUser->department_id !== $department->id) {
                    $ticket->assigned_to = null;
                }
            }
            
            $ticket->save();
            
            // Registrar la acción en los comentarios
            $ticket->comments()->create([
                'user_id' => auth()->guard('web')->id(),
                'content' => "Ticket asignado al departamento: {$department->name}"
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar ticket a departamento: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Asigna un ticket a un usuario específico
     *
     * @param Ticket $ticket
     * @param User $user
     * @return Ticket
     */
    public function assignToUser(Ticket $ticket, User $user): Ticket
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el usuario pertenece al departamento asignado
            if ($ticket->department_id && $user->department_id !== $ticket->department_id) {
                throw new Exception("El usuario no pertenece al departamento asignado al ticket");
            }
            
            // Actualizar el ticket con el usuario asignado
            $ticket->assigned_to = $user->id;
            
            // Si no tenía departamento asignado, asignamos el del usuario
            if (!$ticket->department_id) {
                $ticket->department_id = $user->department_id;
            }
            
            $ticket->save();
            
            // Registrar la acción en los comentarios
            $ticket->comments()->create([
                'user_id' => auth()->guard('web')->id(),
                'content' => "Ticket asignado al usuario: {$user->name}"
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar ticket a usuario: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Permite a un usuario auto-asignarse un ticket
     *
     * @param Ticket $ticket
     * @param User $user
     * @return Ticket
     */
    public function selfAssign(Ticket $ticket, User $user): Ticket
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el ticket está asignado al departamento del usuario
            // o que no tiene departamento asignado
            if ($ticket->department_id && $ticket->department_id !== $user->department_id) {
                throw new Exception("No puedes auto-asignarte este ticket porque pertenece a otro departamento");
            }
            
            // Actualizar el ticket con el usuario asignado
            $ticket->assigned_to = $user->id;
            
            // Si no tenía departamento asignado, asignamos el del usuario
            if (!$ticket->department_id) {
                $ticket->department_id = $user->department_id;
            }
            
            $ticket->save();
            
            // Registrar la acción en los comentarios
            $ticket->comments()->create([
                'user_id' => $user->id,
                'content' => "Ticket auto-asignado por: {$user->name}"
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error en auto-asignación de ticket: ' . $e->getMessage());
            throw $e;
        }
    }
}
