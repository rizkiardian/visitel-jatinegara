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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE visit_report ALTER COLUMN document_file_url TYPE json USING (CASE WHEN document_file_url IS NULL THEN NULL ELSE to_json(document_file_url) END);');
        } else {
            Schema::table('visit_report', function (Blueprint $table) {
                $table->json('document_file_url')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE visit_report ALTER COLUMN document_file_url TYPE varchar(255) USING document_file_url::text;');
        } else {
            Schema::table('visit_report', function (Blueprint $table) {
                $table->string('document_file_url')->nullable()->change();
            });
        }
    }
};
