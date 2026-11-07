<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified Consolidated Schema Migration for RDB PCRU (Laravel 12).
     * Creates all 81 base tables (if not already imported via SQL dump) and compatibility views.
     */
    public function up(): void
    {
        if (!Schema::hasTable('rdb_project') || !Schema::hasTable('system_settings') || !Schema::hasTable('rdb_review_template')) {
            $sqlPath = database_path('schema/legacy_base_tables.sql');
            if (file_exists($sqlPath)) {
                DB::unprepared(file_get_contents($sqlPath));
            }
        }

        // Ensure legacy table/view aliases exist automatically
        if (Schema::hasTable('rdb_project_type----') && !Schema::hasView('rdb_project_type')) {
            DB::statement('CREATE OR REPLACE VIEW `rdb_project_type` AS SELECT * FROM `rdb_project_type----`');
        }
        if (Schema::hasTable('rdb_strategic---') && !Schema::hasView('rdb_strategic')) {
            DB::statement('CREATE OR REPLACE VIEW `rdb_strategic` AS SELECT * FROM `rdb_strategic---`');
        }

        // Ensure default 6 master funding categories exist if table is empty
        if (Schema::hasTable('rdb_project_types_master') && DB::table('rdb_project_types_master')->count() === 0) {
            $masters = [
                1 => ['master_pt_name' => 'ววน. / สกสว.', 'pttg_id' => 1, 'order_index' => 1, 'color_code' => '#3B82F6'],
                2 => ['master_pt_name' => 'แหล่งทุนภายนอก', 'pttg_id' => 2, 'order_index' => 2, 'color_code' => '#F59E0B'],
                3 => ['master_pt_name' => 'ทุนภายในคณะ', 'pttg_id' => 3, 'order_index' => 3, 'color_code' => '#10B981'],
                4 => ['master_pt_name' => 'รายได้มหาวิทยาลัย', 'pttg_id' => 3, 'order_index' => 4, 'color_code' => '#8B5CF6'],
                5 => ['master_pt_name' => 'ทุนวิจัยสถาบัน', 'pttg_id' => 3, 'order_index' => 5, 'color_code' => '#06B6D4'],
                6 => ['master_pt_name' => 'งบประมาณส่วนตัว', 'pttg_id' => 4, 'order_index' => 6, 'color_code' => '#64748B'],
            ];

            foreach ($masters as $id => $data) {
                DB::table('rdb_project_types_master')->updateOrInsert(
                    ['master_pt_id' => $id],
                    array_merge($data, ['created_at' => now(), 'updated_at' => now()])
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Consolidated production schema is preserved on rollback
    }
};
