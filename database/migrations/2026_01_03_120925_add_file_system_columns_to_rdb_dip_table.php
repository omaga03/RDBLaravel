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
        if (!Schema::hasTable('rdb_dip')) {
            return;
        }

        Schema::table('rdb_dip', function (Blueprint $table) {
            if (!Schema::hasColumn('rdb_dip', 'dip_safe_name')) {
                $table->string('dip_safe_name')->nullable()->after('dip_files');
            }
            if (!Schema::hasColumn('rdb_dip', 'dip_path')) {
                $table->string('dip_path')->nullable()->after('dip_safe_name');
            }
            if (!Schema::hasColumn('rdb_dip', 'dip_size')) {
                $table->unsignedBigInteger('dip_size')->nullable()->after('dip_path');
            }
            if (!Schema::hasColumn('rdb_dip', 'dip_mime_type')) {
                $table->string('dip_mime_type', 100)->nullable()->after('dip_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_dip')) {
            return;
        }

        Schema::table('rdb_dip', function (Blueprint $table) {
            foreach (['dip_safe_name', 'dip_path', 'dip_size', 'dip_mime_type'] as $col) {
                if (Schema::hasColumn('rdb_dip', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
