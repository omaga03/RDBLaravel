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
        // 1. rdb_published indexes
        if (Schema::hasTable('rdb_published')) {
            Schema::table('rdb_published', function (Blueprint $table) {
                if (!Schema::hasIndex('rdb_published', 'idx_pub_data_show_date')) {
                    $table->index(['data_show', 'pub_date'], 'idx_pub_data_show_date');
                }
                if (!Schema::hasIndex('rdb_published', 'idx_pub_type_id')) {
                    $table->index('pubtype_id', 'idx_pub_type_id');
                }
                if (!Schema::hasIndex('rdb_published', 'idx_pub_data_show_year')) {
                    $table->index(['data_show', 'year_id'], 'idx_pub_data_show_year');
                }
            });
        }

        // 2. rdb_project indexes
        if (Schema::hasTable('rdb_project')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                if (!Schema::hasIndex('rdb_project', 'idx_pro_data_show_date')) {
                    $table->index(['data_show', 'pro_date_start'], 'idx_pro_data_show_date');
                }
                if (!Schema::hasIndex('rdb_project', 'idx_pro_data_show_year')) {
                    $table->index(['data_show', 'year_id'], 'idx_pro_data_show_year');
                }
                if (!Schema::hasIndex('rdb_project', 'idx_pro_data_show_pt')) {
                    $table->index(['data_show', 'pt_id'], 'idx_pro_data_show_pt');
                }
            });
        }

        // 3. rdb_dip indexes
        if (Schema::hasTable('rdb_dip')) {
            Schema::table('rdb_dip', function (Blueprint $table) {
                if (!Schema::hasIndex('rdb_dip', 'idx_dip_data_show')) {
                    $table->index('data_show', 'idx_dip_data_show');
                }
            });
        }

        // 4. rdb_project_utilize indexes
        if (Schema::hasTable('rdb_project_utilize')) {
            Schema::table('rdb_project_utilize', function (Blueprint $table) {
                if (!Schema::hasIndex('rdb_project_utilize', 'idx_util_data_show')) {
                    $table->index('data_show', 'idx_util_data_show');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rdb_published')) {
            Schema::table('rdb_published', function (Blueprint $table) {
                foreach (['idx_pub_data_show_date', 'idx_pub_type_id', 'idx_pub_data_show_year'] as $idx) {
                    if (Schema::hasIndex('rdb_published', $idx)) {
                        $table->dropIndex($idx);
                    }
                }
            });
        }

        if (Schema::hasTable('rdb_project')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                foreach (['idx_pro_data_show_date', 'idx_pro_data_show_year', 'idx_pro_data_show_pt'] as $idx) {
                    if (Schema::hasIndex('rdb_project', $idx)) {
                        $table->dropIndex($idx);
                    }
                }
            });
        }

        if (Schema::hasTable('rdb_dip')) {
            Schema::table('rdb_dip', function (Blueprint $table) {
                if (Schema::hasIndex('rdb_dip', 'idx_dip_data_show')) {
                    $table->dropIndex('idx_dip_data_show');
                }
            });
        }

        if (Schema::hasTable('rdb_project_utilize')) {
            Schema::table('rdb_project_utilize', function (Blueprint $table) {
                if (Schema::hasIndex('rdb_project_utilize', 'idx_util_data_show')) {
                    $table->dropIndex('idx_util_data_show');
                }
            });
        }
    }
};
