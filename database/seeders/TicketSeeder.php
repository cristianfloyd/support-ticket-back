<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use App\Models\{Ticket, User, Comment, Attachment};

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->get();

        // Crear 50 tickets de prueba
        Ticket::factory(50)->create()->each(function ($ticket) use ($agents) {
            // Asignar aleatoriamente a un agente
            $ticket->assigned_to = $agents->random()->id;
            $ticket->save();

            // Crear algunos comentarios
            Comment::factory(rand(1, 5))->create([
                'ticket_id' => $ticket->id
            ]);

            // Crear algunos adjuntos
            if (rand(0, 1)) {
                Attachment::factory(rand(1, 3))->create([
                    'original_name' => $ticket->title,
                    'ticket_id' => $ticket->id
                ]);
            }
        });
    }
}