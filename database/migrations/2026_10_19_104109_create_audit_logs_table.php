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
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 255)->nullable();
                $table->string('user_role', 100)->nullable();
                $table->string('scope_info', 255)->nullable();
                $table->string('event', 50)->index(); // create, update, delete, approve, reject, impersonate, export
                $table->string('auditable_type', 191)->index();
                $table->unsignedBigInteger('auditable_id')->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('diff_summary')->nullable();
                $table->text('url')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['auditable_type', 'auditable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
