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
                if (!Schema::hasColumn($tableName, 'workflow_status')) {
                    $table->string('workflow_status', 32)->default('published')->index();
                }
                if (!Schema::hasColumn($tableName, 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'reviewed_by')) {
                    $table->unsignedBigInteger('reviewed_by')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'rejection_remarks')) {
                    $table->text('rejection_remarks')->nullable();
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
                $columns = ['workflow_status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'rejection_remarks'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
