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
        Schema::table('events', function (Blueprint $table) {
            $table->string('email_subject')->nullable()->after('logo');
            $table->longText('email_template_html')->nullable()->after('email_subject');
            $table->longText('email_template_mjml')->nullable()->after('email_template_html');
        });

        Schema::table('document_configurations', function (Blueprint $table) {
            $table->string('email_subject')->nullable()->after('email_message');
            $table->longText('email_template_html')->nullable()->after('email_subject');
            $table->longText('email_template_mjml')->nullable()->after('email_template_html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['email_subject', 'email_template_html', 'email_template_mjml']);
        });

        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn(['email_subject', 'email_template_html', 'email_template_mjml']);
        });
    }
};
