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
            if (!Schema::hasColumn('document_configurations', 'folio_start')) {
                $table->integer('folio_start')->default(1)->after('sample_data');
            }
            if (!Schema::hasColumn('document_configurations', 'folio_digits')) {
                $table->integer('folio_digits')->default(4)->after('folio_start');
            }
            if (!Schema::hasColumn('document_configurations', 'folio_year_prefix')) {
                $table->boolean('folio_year_prefix')->default(false)->after('folio_digits');
            }
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
