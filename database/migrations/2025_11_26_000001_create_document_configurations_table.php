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
        Schema::create('document_configurations', function (Blueprint $table) {
            $table->id();

            // Información general del documento
            $table->string('document_type'); // constancia, gafete, carta_aceptacion, carta_rechazo, etc.
            $table->string('document_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Configuración de página
            $table->enum('page_orientation', ['P', 'L'])->default('P'); // Portrait, Landscape
            $table->string('page_size')->default('Letter'); // Letter, A4, etc.
            $table->string('page_unit')->default('mm'); // mm, pt, etc.

            // Imágenes de fondo
            $table->string('background_image')->nullable();
            $table->decimal('background_x', 8, 2)->default(0);
            $table->decimal('background_y', 8, 2)->default(0);
            $table->decimal('background_width', 8, 2)->default(210);
            $table->decimal('background_height', 8, 2)->default(297);

            // Configuración de QR
            $table->boolean('show_qr')->default(true);
            $table->decimal('qr_x', 8, 2)->default(210);
            $table->decimal('qr_y', 8, 2)->default(158);
            $table->decimal('qr_width', 8, 2)->default(30);
            $table->decimal('qr_height', 8, 2)->default(30);

            // Configuración de textos
            $table->json('text_elements')->nullable(); // Array de elementos de texto con posiciones

            // Configuración de fuentes
            $table->string('default_font_family')->default('Arial');
            $table->string('default_font_size')->default('12');
            $table->string('default_font_style')->default(''); // B, I, U, etc.

            // Configuración de colores
            $table->string('default_text_color')->default('#000000');
            $table->string('default_fill_color')->default('#FFFFFF');

            // Configuración de márgenes y espaciado
            $table->decimal('margin_top', 8, 2)->default(10);
            $table->decimal('margin_bottom', 8, 2)->default(10);
            $table->decimal('margin_left', 8, 2)->default(10);
            $table->decimal('margin_right', 8, 2)->default(10);

            // Configuración de preview
            $table->boolean('enable_live_preview')->default(true);
            $table->integer('preview_delay')->default(1000); // milisegundos

            // Datos de ejemplo para preview
            $table->json('sample_data')->nullable(); // Datos de ejemplo para preview

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_configurations');
    }
};
