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
        if (Schema::hasTable('user') && !Schema::hasColumn('user', 'notification_preferences')) {
            Schema::table('user', function (Blueprint $table) {
                $table->json('notification_preferences')->nullable()->after('telegram_chat_id')->comment('การตั้งค่าช่องทางและหัวข้อการแจ้งเตือนส่วนบุคคล (Mail & Telegram)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user') && Schema::hasColumn('user', 'notification_preferences')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('notification_preferences');
            });
        }
    }
};
