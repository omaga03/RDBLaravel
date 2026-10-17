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
        Schema::table('rdb_published', function (Blueprint $table) {
            $table->string('pf_safe_name')->nullable()->after('pub_file');
            $table->string('pf_path')->nullable()->after('pf_safe_name');
            $table->integer('pf_size')->nullable()->after('pf_path');
            $table->string('pf_mime_type', 100)->nullable()->after('pf_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_published', function (Blueprint $table) {
            $table->dropColumn(['pf_safe_name', 'pf_path', 'pf_size', 'pf_mime_type']);
        });
    }
};
