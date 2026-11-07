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
        if (!Schema::hasTable('research_news')) {
            return;
        }

        Schema::table('research_news', function (Blueprint $table) {
            if (!Schema::hasColumn('research_news', 'news_safe_name')) {
                $table->string('news_safe_name')->nullable()->after('news_img');
            }
            if (!Schema::hasColumn('research_news', 'news_path')) {
                $table->string('news_path')->nullable()->after('news_safe_name');
            }
            if (!Schema::hasColumn('research_news', 'news_size')) {
                $table->unsignedBigInteger('news_size')->nullable()->after('news_path');
            }
            if (!Schema::hasColumn('research_news', 'news_mime_type')) {
                $table->string('news_mime_type', 100)->nullable()->after('news_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('research_news')) {
            return;
        }

        Schema::table('research_news', function (Blueprint $table) {
            foreach (['news_safe_name', 'news_path', 'news_size', 'news_mime_type'] as $col) {
                if (Schema::hasColumn('research_news', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
