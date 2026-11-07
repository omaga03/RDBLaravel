<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure the base legacy tables exist when migrating on an empty database.
     * If the legacy database (e.g. rdb_project) is already imported, this migration safely skips.
     */
    public function up(): void
    {
        if (!Schema::hasTable('rdb_project')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Base legacy tables are preserved on rollback
    }
};