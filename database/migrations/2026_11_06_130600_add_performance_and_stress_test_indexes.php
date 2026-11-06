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
        Schema::table('rdb_published', function (Blueprint $table) {
            $table->index(['data_show', 'pub_date'], 'idx_pub_data_show_date');
            $table->index('pubtype_id', 'idx_pub_type_id');
            $table->index(['data_show', 'year_id'], 'idx_pub_data_show_year');
        });

        // 2. rdb_project indexes
        Schema::table('rdb_project', function (Blueprint $table) {
            $table->index(['data_show', 'pro_date_start'], 'idx_pro_data_show_date');
            $table->index(['data_show', 'year_id'], 'idx_pro_data_show_year');
            $table->index(['data_show', 'pt_id'], 'idx_pro_data_show_pt');
        });

        // 3. rdb_dip indexes
        Schema::table('rdb_dip', function (Blueprint $table) {
            $table->index('data_show', 'idx_dip_data_show');
        });

        // 4. rdb_project_utilize indexes
        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            $table->index('data_show', 'idx_util_data_show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_published', function (Blueprint $table) {
            $table->dropIndex('idx_pub_data_show_date');
            $table->dropIndex('idx_pub_type_id');
            $table->dropIndex('idx_pub_data_show_year');
        });

        Schema::table('rdb_project', function (Blueprint $table) {
            $table->dropIndex('idx_pro_data_show_date');
            $table->dropIndex('idx_pro_data_show_year');
            $table->dropIndex('idx_pro_data_show_pt');
        });

        Schema::table('rdb_dip', function (Blueprint $table) {
            $table->dropIndex('idx_dip_data_show');
        });

        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            $table->dropIndex('idx_util_data_show');
        });
    }
};
