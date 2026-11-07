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
        if (!Schema::hasTable('research_coferenceinthai')) {
            return;
        }

        Schema::table('research_coferenceinthai', function (Blueprint $table) {
            if (!Schema::hasColumn('research_coferenceinthai', 'con_safe_name')) {
                $table->string('con_safe_name')->nullable()->after('con_img');
            }
            if (!Schema::hasColumn('research_coferenceinthai', 'con_path')) {
                $table->string('con_path')->nullable()->after('con_safe_name');
            }
            if (!Schema::hasColumn('research_coferenceinthai', 'con_size')) {
                $table->integer('con_size')->nullable()->after('con_path');
            }
            if (!Schema::hasColumn('research_coferenceinthai', 'con_mime_type')) {
                $table->string('con_mime_type', 100)->nullable()->after('con_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('research_coferenceinthai')) {
            return;
        }

        Schema::table('research_coferenceinthai', function (Blueprint $table) {
            foreach (['con_safe_name', 'con_path', 'con_size', 'con_mime_type'] as $col) {
                if (Schema::hasColumn('research_coferenceinthai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
