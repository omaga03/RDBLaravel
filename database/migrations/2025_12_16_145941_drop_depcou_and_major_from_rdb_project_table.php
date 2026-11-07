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
        if (!Schema::hasTable('rdb_project')) {
            return;
        }

        // Preserve depcou_id and major_id columns (2,658 historical records exist in rdb_project).
        // Only drop legacy fk_depcou_id foreign key constraint if it still exists.
        $foreignKeys = array_map(fn ($fk) => $fk['name'] ?? '', Schema::getForeignKeys('rdb_project'));
        if (in_array('fk_depcou_id', $foreignKeys, true)) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $table->dropForeign('fk_depcou_id');
            });
        }
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
