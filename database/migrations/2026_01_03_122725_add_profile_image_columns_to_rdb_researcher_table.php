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
        if (!Schema::hasTable('rdb_researcher')) {
            return;
        }

        Schema::table('rdb_researcher', function (Blueprint $table) {
            if (!Schema::hasColumn('rdb_researcher', 'rf_safe_name')) {
                $table->string('rf_safe_name')->nullable()->after('researcher_picture');
            }
            if (!Schema::hasColumn('rdb_researcher', 'rf_path')) {
                $table->string('rf_path')->nullable()->after('rf_safe_name');
            }
            if (!Schema::hasColumn('rdb_researcher', 'rf_size')) {
                $table->unsignedBigInteger('rf_size')->nullable()->after('rf_path');
            }
            if (!Schema::hasColumn('rdb_researcher', 'rf_mime_type')) {
                $table->string('rf_mime_type', 100)->nullable()->after('rf_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rdb_researcher')) {
            return;
        }

        Schema::table('rdb_researcher', function (Blueprint $table) {
            foreach (['rf_safe_name', 'rf_path', 'rf_size', 'rf_mime_type'] as $col) {
                if (Schema::hasColumn('rdb_researcher', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
