<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('rdb_researcher', 'data_show')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->tinyInteger('data_show')->default(1)->after('scopus_synced_at')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rdb_researcher', 'data_show')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                $table->dropColumn('data_show');
            });
        }
    }
};
