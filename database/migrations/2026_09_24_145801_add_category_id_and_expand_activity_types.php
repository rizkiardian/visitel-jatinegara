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
        Schema::table('activity_type', function (Blueprint $table) {
            $table->foreignId('activity_category_id')->nullable()->after('name')->constrained('activity_category')->nullOnDelete();
        });

        // Update existing IDs 1 to 5 to keep historical records intact and descriptive
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 1)->update([
            'name' => 'Visit Perdana (First Meet & Profiling)',
            'activity_category_id' => 2, // Approaching
        ]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 2)->update([
            'name' => 'Demo Produk & Solusi (PoC / Presentasi Teknis)',
            'activity_category_id' => 3, // Dealing
        ]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 3)->update([
            'name' => 'Follow Up Prospek (Gali Kebutuhan ICT)',
            'activity_category_id' => 2, // Approaching
        ]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 4)->update([
            'name' => 'Penagihan Invoice Rutin / Piutang (Collection)',
            'activity_category_id' => 1, // Aftersales
        ]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 5)->update([
            'name' => 'Maintenance Relasi & Evaluasi SLA',
            'activity_category_id' => 1, // Aftersales
        ]);

        // Insert new specific activity types
        $now = now();
        $newTypes = [
            // Dealing (ID 3)
            [
                'name' => 'Diskusi & Negosiasi Penawaran (SPH)',
                'activity_category_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Penandatanganan Kontrak (Closing PKS)',
                'activity_category_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Penagihan DP / Pembayaran Awal Proyek',
                'activity_category_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Aftersales (ID 1)
            [
                'name' => 'Serah Terima Layanan (Dokumen BASO)',
                'activity_category_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Penanganan Komplain (Complain Handling)',
                'activity_category_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Perpanjangan / Upgrade Layanan (Renewal & Upsell)',
                'activity_category_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        \Illuminate\Support\Facades\DB::table('activity_type')->insert($newTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', '>', 5)->delete();

        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 1)->update(['name' => 'Visit Perdana', 'activity_category_id' => null]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 2)->update(['name' => 'Demo', 'activity_category_id' => null]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 3)->update(['name' => 'Follow Up', 'activity_category_id' => null]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 4)->update(['name' => 'Penagihan', 'activity_category_id' => null]);
        \Illuminate\Support\Facades\DB::table('activity_type')->where('id', 5)->update(['name' => 'Maintenance', 'activity_category_id' => null]);

        Schema::table('activity_type', function (Blueprint $table) {
            $table->dropForeign(['activity_category_id']);
            $table->dropColumn('activity_category_id');
        });
    }
};
