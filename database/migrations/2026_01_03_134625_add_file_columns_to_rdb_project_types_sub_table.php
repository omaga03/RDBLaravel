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
        // Schema::table('rdb_project_types_sub', function (Blueprint $table) {
        //     $table->string('pts_safe_name')->nullable()->after('pts_file');
        //     $table->string('pts_path')->nullable()->after('pts_safe_name');
        //     $table->integer('pts_size')->nullable()->after('pts_path');
        //     $table->string('pts_mime_type', 100)->nullable()->after('pts_size');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('rdb_project_types_sub', function (Blueprint $table) {
        //     $table->dropColumn(['pts_safe_name', 'pts_path', 'pts_size', 'pts_mime_type']);
        // });
    }
};
