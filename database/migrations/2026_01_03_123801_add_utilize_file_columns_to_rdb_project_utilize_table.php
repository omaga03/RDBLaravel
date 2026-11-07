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
        if (!Schema::hasTable('rdb_project_utilize')) {
            return;
        }

        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            if (!Schema::hasColumn('rdb_project_utilize', 'uf_safe_name')) {
                $table->string('uf_safe_name')->nullable()->after('utz_files');
            }
            if (!Schema::hasColumn('rdb_project_utilize', 'uf_path')) {
                $table->string('uf_path')->nullable()->after('uf_safe_name');
            }
            if (!Schema::hasColumn('rdb_project_utilize', 'uf_size')) {
                $table->unsignedBigInteger('uf_size')->nullable()->after('uf_path');
            }
            if (!Schema::hasColumn('rdb_project_utilize', 'uf_mime_type')) {
                $table->string('uf_mime_type', 100)->nullable()->after('uf_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_project_utilize')) {
            return;
        }

        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            foreach (['uf_safe_name', 'uf_path', 'uf_size', 'uf_mime_type'] as $col) {
                if (Schema::hasColumn('rdb_project_utilize', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
