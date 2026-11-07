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
        if (!Schema::hasTable('rdb_project_types_sub')) {
            return;
        }

        Schema::table('rdb_project_types_sub', function (Blueprint $table) {
            if (!Schema::hasColumn('rdb_project_types_sub', 'pts_safe_name')) {
                $table->string('pts_safe_name')->nullable()->after('pts_file');
            }
            if (!Schema::hasColumn('rdb_project_types_sub', 'pts_path')) {
                $table->string('pts_path')->nullable()->after('pts_safe_name');
            }
            if (!Schema::hasColumn('rdb_project_types_sub', 'pts_size')) {
                $table->unsignedBigInteger('pts_size')->nullable()->after('pts_path');
            }
            if (!Schema::hasColumn('rdb_project_types_sub', 'pts_mime_type')) {
                $table->string('pts_mime_type', 100)->nullable()->after('pts_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_project_types_sub')) {
            return;
        }

        Schema::table('rdb_project_types_sub', function (Blueprint $table) {
            foreach (['pts_safe_name', 'pts_path', 'pts_size', 'pts_mime_type'] as $col) {
                if (Schema::hasColumn('rdb_project_types_sub', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
