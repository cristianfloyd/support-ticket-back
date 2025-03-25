<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->foreignId('department_id')->nullable()->after('password')->constrained();
            $table->boolean('is_active')->default(true)->after('department_id');
            $table->softDeletes();

            // Crear nuevos índices
            $table->index(['email', 'deleted_at']);
            $table->index(['department_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['email', 'deleted_at']);
            $table->dropIndex(['department_id', 'is_active']);

            // Eliminar columnas
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'is_active', 'deleted_at']);
        });
    }
};