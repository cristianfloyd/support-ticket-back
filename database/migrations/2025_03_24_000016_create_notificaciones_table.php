<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificacionesTable extends Migration
{
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->index()
                ->name('fk_notificaciones_user');
            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->index()
                ->name('fk_notificaciones_ticket');
            $table->string('type')->index();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}