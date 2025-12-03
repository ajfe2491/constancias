<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->boolean('background_fit')->default(false)->after('background_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn('background_fit');
        });
    }
};
