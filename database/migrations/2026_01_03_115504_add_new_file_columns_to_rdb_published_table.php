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
        if (!Schema::hasTable('rdb_published')) {
            return;
        }

        Schema::table('rdb_published', function (Blueprint $table) {
            if (!Schema::hasColumn('rdb_published', 'pf_safe_name')) {
                $table->string('pf_safe_name')->nullable()->after('pub_file');
            }
            if (!Schema::hasColumn('rdb_published', 'pf_path')) {
                $table->string('pf_path')->nullable()->after('pf_safe_name');
            }
            if (!Schema::hasColumn('rdb_published', 'pf_size')) {
                $table->unsignedBigInteger('pf_size')->nullable()->after('pf_path');
            }
            if (!Schema::hasColumn('rdb_published', 'pf_mime_type')) {
                $table->string('pf_mime_type', 100)->nullable()->after('pf_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_published')) {
            return;
        }

        Schema::table('rdb_published', function (Blueprint $table) {
            foreach (['pf_safe_name', 'pf_path', 'pf_size', 'pf_mime_type'] as $col) {
                if (Schema::hasColumn('rdb_published', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
