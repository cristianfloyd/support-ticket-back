<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\{Ticket, User, Comment, Attachment};

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->get();

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

        // Crear 50 tickets de prueba
        Ticket::factory(50)->create()->each(function ($ticket) use ($agents, $examplePath) {
            // Asignar aleatoriamente a un agente
            $ticket->assigned_to = $agents->random()->id;
            $ticket->save();

            // Crear algunos comentarios
            Comment::factory(rand(1, 5))->create([
                'ticket_id' => $ticket->id
            ]);

            // Crear algunos adjuntos con archivo real
            if (rand(0, 1)) {
                $numAttachments = rand(1, 3);
                
                for ($i = 0; $i < $numAttachments; $i++) {
                    // Primero, determinar la ruta donde se almacenará el archivo
                    $tempPath = 'public/ticket-attachments/' . uniqid() . '-ejemplo.pdf';
                    
                    // Copiar el archivo a la ubicación temporal para tener una ruta válida
                    Storage::copy('public/ejemplo.pdf', $tempPath);
                    $fullPath = Storage::path($tempPath);
                    
                    // Crear el registro del attachment con un valor para 'path'
                    $attachment = Attachment::create([
                        'ticket_id' => $ticket->id,
                        'filename' => 'ejemplo.pdf',
                        'original_name' => "Adjunto". ($i+1) . " - {$ticket->title}.pdf",
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
        });
    }
}