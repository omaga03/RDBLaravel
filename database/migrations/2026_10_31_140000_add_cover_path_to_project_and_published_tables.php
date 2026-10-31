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
        if (Schema::hasTable('rdb_project') && !Schema::hasColumn('rdb_project', 'pro_cover_path')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $table->string('pro_cover_path', 255)->nullable()->after('pro_file_mime')->comment('พาธภาพประกอบหน้าแรกของเล่มรายงานวิจัยฉบับสมบูรณ์ (.webp)');
            });
        }

        if (Schema::hasTable('rdb_published') && !Schema::hasColumn('rdb_published', 'pub_cover_path')) {
            Schema::table('rdb_published', function (Blueprint $table) {
                $table->string('pub_cover_path', 255)->nullable()->after('pf_mime_type')->comment('พาธภาพประกอบหน้าแรกของไฟล์ผลงานตีพิมพ์ (.webp)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rdb_project') && Schema::hasColumn('rdb_project', 'pro_cover_path')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $table->dropColumn('pro_cover_path');
            });
        }

        if (Schema::hasTable('rdb_published') && Schema::hasColumn('rdb_published', 'pub_cover_path')) {
            Schema::table('rdb_published', function (Blueprint $table) {
                $table->dropColumn('pub_cover_path');
            });
        }
    }
};
