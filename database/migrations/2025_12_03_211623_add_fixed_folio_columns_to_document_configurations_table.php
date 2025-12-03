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
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->boolean('show_folio')->default(false)->after('folio_year_prefix');
            $table->decimal('folio_x', 8, 2)->default(10)->after('show_folio');
            $table->decimal('folio_y', 8, 2)->default(10)->after('folio_x');
            $table->decimal('folio_width', 8, 2)->default(50)->after('folio_y');
            $table->decimal('folio_height', 8, 2)->default(10)->after('folio_width');
            $table->integer('folio_font_size')->default(12)->after('folio_height');
            $table->string('folio_color')->default('#000000')->after('folio_font_size');
            $table->string('folio_alignment')->default('C')->after('folio_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn([
                'show_folio',
                'folio_x',
                'folio_y',
                'folio_width',
                'folio_height',
                'folio_font_size',
                'folio_color',
                'folio_alignment',
            ]);
        });
    }
};
