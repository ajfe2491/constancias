<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('constancy_general_history', function (Blueprint $table) {
            $table->id();
            $table->integer('total_registros');
            $table->integer('procesados_exitosos');
            $table->integer('procesados_fallidos');
            $table->integer('qrs_generados');
            $table->text('errores')->nullable(); // JSON con errores si los hay
            $table->integer('user_id')->nullable(); // Usuario que procesó
            $table->string('csv_file_path')->nullable(); // Ruta del archivo CSV
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancy_general_history');
    }
};
