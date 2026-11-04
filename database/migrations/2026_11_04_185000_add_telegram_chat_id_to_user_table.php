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
        if (Schema::hasTable('user') && !Schema::hasColumn('user', 'telegram_chat_id')) {
            Schema::table('user', function (Blueprint $table) {
                $table->string('telegram_chat_id', 64)->nullable()->after('email')->index()->comment('Telegram Chat ID สำหรับรับการแจ้งเตือนส่วนบุคคล');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user') && Schema::hasColumn('user', 'telegram_chat_id')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('telegram_chat_id');
            });
        }
    }
};
