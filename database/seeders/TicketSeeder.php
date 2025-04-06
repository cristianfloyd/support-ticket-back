<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\{Ticket, User, Comment, Attachment, Department};

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener agentes para asignación
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->get();
        
        // Obtener departamentos activos para asignación
        $departments = Department::active()->get();
        
        // Si no hay departamentos, crear al menos uno para testing
        if ($departments->isEmpty()) {
            $departments = collect([
                Department::create([
                    'name' => 'Departamento de Soporte',
                    'description' => 'Departamento para pruebas',
                    'is_active' => true
                ])
            ]);
        }

        // Asegurar que el archivo de ejemplo existe
        $examplePath = 'app/public/ejemplo.pdf';
        if (!file_exists(storage_path($examplePath))) {
            // Crear directorio si no existe
            if (!file_exists(dirname(storage_path($examplePath)))) {
                mkdir(dirname(storage_path($examplePath)), 0755, true);
            }
            
            // Si no existe el archivo, creamos uno simple de muestra
            file_put_contents(
                storage_path($examplePath), 
                'Este es un archivo PDF de ejemplo para testing'
            );
        }

        // Crear 50 tickets de prueba con department_id
        for ($i = 0; $i < 50; $i++) {
            // Seleccionar un departamento aleatorio
            $department = $departments->random();
            
            // Crear el ticket con department_id
            $ticket = Ticket::factory()->create([
                'department_id' => $department->id
            ]);
            
            // Asignar aleatoriamente a un agente (50% de probabilidad)
            if (rand(0, 1) && $agents->isNotEmpty()) {
                // Filtrar agentes que pertenecen al departamento asignado
                $departmentAgents = $agents->filter(function ($agent) use ($department) {
                    return $agent->department_id === $department->id;
                });
                
                // Si hay agentes en el departamento, asignar uno
                if ($departmentAgents->isNotEmpty()) {
                    $ticket->assigned_to = $departmentAgents->random()->id;
                } else {
                    // Si no hay agentes en el departamento, intentar asignar uno
                    // y actualizar su departamento
                    $randomAgent = $agents->random();
                    $ticket->assigned_to = $randomAgent->id;
                    
                    // Actualizar el departamento del agente para mantener consistencia
                    $randomAgent->department_id = $department->id;
                    $randomAgent->save();
                }
                
                $ticket->save();
            }

            // Crear algunos comentarios
            Comment::factory(rand(1, 5))->create([
                'ticket_id' => $ticket->id
            ]);

            // Crear algunos adjuntos con archivo real
            if (rand(0, 1)) {
                $numAttachments = rand(1, 3);
                
                for ($j = 0; $j < $numAttachments; $j++) {
                    // Primero, determinar la ruta donde se almacenará el archivo
                    $tempPath = 'public/ticket-attachments/' . uniqid() . '-ejemplo.pdf';
                    
                    // Copiar el archivo a la ubicación temporal para tener una ruta válida
                    Storage::copy('public/ejemplo.pdf', $tempPath);
                    $fullPath = Storage::path($tempPath);
                    
                    // Crear el registro del attachment con un valor para 'path'
                    $attachment = Attachment::create([
                        'ticket_id' => $ticket->id,
                        'filename' => 'ejemplo.pdf',
                        'original_name' => "Adjunto". ($j+1) . " - {$ticket->title}.pdf",
                        'path' => $fullPath, // Proporcionar un valor inicial para path
                        'mime_type' => 'application/pdf',
                        'size' => filesize(storage_path($examplePath))
                    ]);
                    
                    // Agregar el archivo físico usando Spatie Media Library
                    $fileContent = file_get_contents(storage_path($examplePath));
                    
                    $attachment->addMediaFromString($fileContent)
                        ->usingFileName('ejemplo.pdf')
                        ->withCustomProperties([
                            'ticket_id' => $ticket->id,
                            'created_from' => 'seeder'
                        ])
                        ->toMediaCollection('file');
                        
                    // Actualizar campos del attachment con datos reales del media
                    $mediaItem = $attachment->getFirstMedia('file');
                    if ($mediaItem) {
                        $attachment->update([
                            'path' => $mediaItem->getPath(),
                            'mime_type' => $mediaItem->mime_type,
                            'size' => $mediaItem->size
                        ]);
                    }
                    
                    // Eliminar el archivo temporal si ya no es necesario
                    Storage::delete($tempPath);
                }
            }
            
            // Agregar un comentario sobre la asignación al departamento
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'content' => "Ticket asignado al departamento: {$department->name}"
            ]);
            
            // Si se asignó a un agente, agregar un comentario sobre eso también
            if ($ticket->assigned_to) {
                $assignedAgent = User::find($ticket->assigned_to);
                Comment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'content' => "Ticket asignado al agente: {$assignedAgent->name}"
                ]);
            }
        }
    }
}