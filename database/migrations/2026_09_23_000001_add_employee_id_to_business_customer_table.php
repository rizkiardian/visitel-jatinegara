<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_customer', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('status')->constrained('employee')->nullOnDelete();
        });

        // Backfill data: Petakan employee_id dari historis visit_report
        $mappings = DB::table('visit_report')
            ->select('business_customer_id', 'employee_id')
            ->whereNotNull('business_customer_id')
            ->whereNotNull('employee_id')
            ->groupBy('business_customer_id', 'employee_id')
            ->get();

        foreach ($mappings as $map) {
            DB::table('business_customer')
                ->where('id', $map->business_customer_id)
                ->whereNull('employee_id')
                ->update(['employee_id' => $map->employee_id]);
        }

        // Jika ada BC yang belum punya kunjungan, hubungkan berdasarkan telda_id AM
        $unassigned = DB::table('business_customer')->whereNull('employee_id')->get();
        foreach ($unassigned as $bc) {
            $matchedEmp = DB::table('employee')
                ->where('telda_id', $bc->telda_id)
                ->where('is_active', true)
                ->first();

            if ($matchedEmp) {
                DB::table('business_customer')->where('id', $bc->id)->update(['employee_id' => $matchedEmp->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('business_customer', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
