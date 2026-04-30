<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la restricción de unicidad de la base de datos
            // para permitir que correos eliminados lógicamente puedan volver a registrarse.
            // La validación ahora se maneja 100% en el UserController de Laravel.
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restaurar la restricción de unicidad en caso de rollback
            $table->unique('email');
        });
    }
};
