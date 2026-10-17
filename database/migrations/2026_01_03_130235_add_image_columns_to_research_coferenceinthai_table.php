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
        Schema::table('research_coferenceinthai', function (Blueprint $table) {
            $table->string('con_safe_name')->nullable()->after('con_img');
            $table->string('con_path')->nullable()->after('con_safe_name');
            $table->integer('con_size')->nullable()->after('con_path');
            $table->string('con_mime_type', 100)->nullable()->after('con_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_coferenceinthai', function (Blueprint $table) {
            $table->dropColumn(['con_safe_name', 'con_path', 'con_size', 'con_mime_type']);
        });
    }
};
