<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rdb_researcher') && Schema::hasColumn('rdb_researcher', 'researcher_picture')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->string('researcher_picture', 255)->nullable()->change();
            });
        }

        if (Schema::hasTable('research_coferenceinthai') && Schema::hasColumn('research_coferenceinthai', 'con_img')) {
            Schema::table('research_coferenceinthai', function (Blueprint $table) {
                $table->string('con_img', 255)->nullable()->change();
                if (Schema::hasColumn('research_coferenceinthai', 'con_site_img')) {
                    $table->string('con_site_img', 255)->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rdb_researcher') && Schema::hasColumn('rdb_researcher', 'researcher_picture')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->string('researcher_picture', 20)->nullable()->change();
            });
        }

        if (Schema::hasTable('research_coferenceinthai') && Schema::hasColumn('research_coferenceinthai', 'con_img')) {
            Schema::table('research_coferenceinthai', function (Blueprint $table) {
                $table->string('con_img', 30)->nullable()->change();
                if (Schema::hasColumn('research_coferenceinthai', 'con_site_img')) {
                    $table->string('con_site_img', 30)->nullable()->change();
                }
            });
        }
    }
};
