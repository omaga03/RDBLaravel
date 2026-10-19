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
        $tables = ['rdb_project', 'rdb_published', 'rdb_dip', 'rdb_project_utilize'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'unlock_reason')) {
                    $table->text('unlock_reason')->nullable()->after('rejection_remarks');
                }
                if (!Schema::hasColumn($tableName, 'unlock_requested_at')) {
                    $table->timestamp('unlock_requested_at')->nullable()->after('unlock_reason');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['rdb_project', 'rdb_published', 'rdb_dip', 'rdb_project_utilize'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = ['unlock_reason', 'unlock_requested_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
