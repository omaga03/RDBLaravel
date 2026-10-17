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
        Schema::table('rdb_dip', function (Blueprint $table) {
            $table->string('dip_safe_name')->nullable()->after('dip_files');
            $table->string('dip_path')->nullable()->after('dip_safe_name');
            $table->integer('dip_size')->nullable()->after('dip_path');
            $table->string('dip_mime_type', 100)->nullable()->after('dip_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_dip', function (Blueprint $table) {
            $table->dropColumn(['dip_safe_name', 'dip_path', 'dip_size', 'dip_mime_type']);
        });
    }
};
