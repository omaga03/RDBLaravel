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
        // Schema::table('rdb_researcher', function (Blueprint $table) {
        //     $table->string('rf_safe_name')->nullable()->after('researcher_picture');
        //     $table->string('rf_path')->nullable()->after('rf_safe_name');
        //     $table->integer('rf_size')->nullable()->after('rf_path');
        //     $table->string('rf_mime_type', 100)->nullable()->after('rf_size');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('rdb_researcher', function (Blueprint $table) {
        //     $table->dropColumn(['rf_safe_name', 'rf_path', 'rf_size', 'rf_mime_type']);
        // });
    }
};
