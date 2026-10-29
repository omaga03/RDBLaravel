<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rdb_review_criteria')) {
            Schema::table('rdb_review_criteria', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_review_criteria', 'scale_max')) {
                    $table->unsignedInteger('scale_max')->nullable()->after('item_type');
                }
                if (!Schema::hasColumn('rdb_review_criteria', 'scale_step')) {
                    $table->unsignedInteger('scale_step')->default(1)->after('scale_max');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rdb_review_criteria')) {
            Schema::table('rdb_review_criteria', function (Blueprint $table) {
                if (Schema::hasColumn('rdb_review_criteria', 'scale_step')) {
                    $table->dropColumn('scale_step');
                }
                if (Schema::hasColumn('rdb_review_criteria', 'scale_max')) {
                    $table->dropColumn('scale_max');
                }
            });
        }
    }
};
