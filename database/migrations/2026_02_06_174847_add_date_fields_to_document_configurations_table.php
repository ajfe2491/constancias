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
            $table->string('custom_folio_year')->nullable()->after('folio_year_prefix');
            $table->string('issuance_date')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn(['custom_folio_year', 'issuance_date']);
        });
    }
};
