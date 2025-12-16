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
        Schema::table('rdb_project', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign('fk_depcou_id');
            // Then drop columns
            $table->dropColumn(['depcou_id', 'major_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_project', function (Blueprint $table) {
            $table->integer('depcou_id')->nullable();
            $table->integer('major_id')->nullable();
            
            // Restore FK
            $table->foreign('depcou_id', 'fk_depcou_id')
                  ->references('depcou_id')
                  ->on('rdb_department_course')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }
};
