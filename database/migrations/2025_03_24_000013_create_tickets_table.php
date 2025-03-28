<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->foreignId('status_id')
                ->constrained('statuses')
                ->index();
            $table->foreignId('priority_id')
                ->constrained('priorities')
                ->index()
                ->name('fk_tickets_priority');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->index()
                ->name('fk_tickets_category');
            $table->foreignId('user_id')
                ->constrained('users')
                ->index()
                ->name('fk_tickets_user');
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->index()
                ->name('fk_tickets_assigned_user');
            $table->foreignId('unidad_academica_id')
                ->constrained('unidades_academicas')
                ->index()
                ->name('fk_tickets_unidad_academica');
            $table->foreignId('building_id')
                ->constrained('buildings')
                ->index()
                ->name('fk_tickets_building');
            $table->foreignId('office_id')
                ->constrained('offices')
                ->index()
                ->name('fk_tickets_office');
            $table->foreignId('equipment_id')
                ->nullable()
                ->constrained('equipments')
                ->index()
                ->name('fk_tickets_equipment');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_id', 'created_at'], 'idx_tickets_status_created');
            $table->index(['assigned_to', 'status_id'], 'idx_tickets_assigned_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
