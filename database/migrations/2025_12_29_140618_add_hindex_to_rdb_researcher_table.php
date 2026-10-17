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
        // ตรวจสอบว่า column มีอยู่แล้วหรือไม่
        if (Schema::hasTable('rdb_researcher') && !Schema::hasColumn('rdb_researcher', 'researcher_hindex')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->integer('researcher_hindex')->default(0)->after('orcid')->comment('h-index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rdb_researcher') && Schema::hasColumn('rdb_researcher', 'researcher_hindex')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->dropColumn('researcher_hindex');
            });
        }
    }
};
