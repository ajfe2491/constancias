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
            $table->integer('folio_start')->default(1)->after('sample_data');
            $table->integer('folio_digits')->default(4)->after('folio_start');
            $table->boolean('folio_year_prefix')->default(false)->after('folio_digits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn(['folio_start', 'folio_digits', 'folio_year_prefix']);
        });
    }
};
