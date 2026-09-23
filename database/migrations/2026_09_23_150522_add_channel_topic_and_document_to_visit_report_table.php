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
        Schema::table('visit_report', function (Blueprint $table) {
            $table->string('communication_channel')->nullable()->after('visit_type');
            $table->string('activity_topic')->nullable()->after('communication_channel');
            $table->string('document_file_url')->nullable()->after('activity_topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_report', function (Blueprint $table) {
            $table->dropColumn(['communication_channel', 'activity_topic', 'document_file_url']);
        });
    }
};
