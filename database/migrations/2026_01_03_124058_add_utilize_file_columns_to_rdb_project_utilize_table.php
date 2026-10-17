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
        // Schema::table('rdb_project_utilize', function (Blueprint $table) {
        //     $table->string('uf_safe_name')->nullable()->after('utz_files');
        //     $table->string('uf_path')->nullable()->after('uf_safe_name');
        //     $table->integer('uf_size')->nullable()->after('uf_path');
        //     $table->string('uf_mime_type', 100)->nullable()->after('uf_size');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('rdb_project_utilize', function (Blueprint $table) {
        //     $table->dropColumn(['uf_safe_name', 'uf_path', 'uf_size', 'uf_mime_type']);
        // });
    }
};
