<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Business rules for `pro_file_show`:
     * 0 = เปิด (Public / Open / เผยแพร่สาธารณะ)
     * 1 = ปิด (Private / Closed / ปกปิดเล่มรายงาน)
     * 2 = ร่าง (Draft / ฉบับร่าง)
     */
    public function up(): void
    {
        $legacyConcealedIds = [831, 1483, 2186, 2187, 2188, 2189, 2190, 2191, 2471, 2571];

        // 1. In legacy Yii2, projects without uploaded report stored string '0' in pro_file. Clean them to NULL.
        DB::table('rdb_project')
            ->where('pro_file', '0')
            ->update([
                'pro_file' => null,
                'pro_file_show' => 0,
            ]);

        // 2. Set all projects that have a real report file to 0 = เปิด, except the legacy concealed ones
        DB::table('rdb_project')
            ->whereNotNull('pro_file')
            ->where('pro_file', '!=', '')
            ->where('pro_file', '!=', '0')
            ->whereNotIn('pro_id', $legacyConcealedIds)
            ->update(['pro_file_show' => 0]);

        // 3. Set the legacy concealed ones to 1 = ปิด
        DB::table('rdb_project')
            ->whereIn('pro_id', $legacyConcealedIds)
            ->update(['pro_file_show' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep consistent
    }
};
