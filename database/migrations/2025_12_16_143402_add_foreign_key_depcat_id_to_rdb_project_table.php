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
        if (!Schema::hasTable('rdb_project') || !Schema::hasTable('rdb_department_category')) {
            return;
        }

        $foreignKeys = array_map(fn ($fk) => $fk['name'] ?? '', Schema::getForeignKeys('rdb_project'));
        if (!in_array('fk_rdb_project_depcat_id', $foreignKeys, true)) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $table->foreign('depcat_id', 'fk_rdb_project_depcat_id')
                      ->references('depcat_id')
                      ->on('rdb_department_category')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_project')) {
            return;
        }

        $foreignKeys = array_map(fn ($fk) => $fk['name'] ?? '', Schema::getForeignKeys('rdb_project'));
        if (in_array('fk_rdb_project_depcat_id', $foreignKeys, true)) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $table->dropForeign('fk_rdb_project_depcat_id');
            });
        }
    }
};
