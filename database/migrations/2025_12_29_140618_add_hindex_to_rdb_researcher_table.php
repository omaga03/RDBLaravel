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
        Schema::table('rdb_researcher', function (Blueprint $table) {
            $table->integer('researcher_hindex')->default(0)->after('orcid');
            $table->timestamp('scopus_synced_at')->nullable()->after('researcher_hindex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_researcher', function (Blueprint $table) {
            $table->dropColumn(['researcher_hindex', 'scopus_synced_at']);
        });
    }
};
