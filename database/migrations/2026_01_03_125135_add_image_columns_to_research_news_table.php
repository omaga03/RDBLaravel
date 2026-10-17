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
        // Schema::table('research_news', function (Blueprint $table) {
        //     $table->string('news_safe_name')->nullable()->after('news_img');
        //     $table->string('news_path')->nullable()->after('news_safe_name');
        //     $table->integer('news_size')->nullable()->after('news_path');
        //     $table->string('news_mime_type', 100)->nullable()->after('news_size');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('research_news', function (Blueprint $table) {
        //     $table->dropColumn(['news_safe_name', 'news_path', 'news_size', 'news_mime_type']);
        // });
    }
};
