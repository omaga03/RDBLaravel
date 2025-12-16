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
            // Ensure the column is indexed (foreign keys technically create one, but good to be explicit or just let FK do it)
            // Adding Foreign Key
            // Assuming rdb_department_category is the table name and depcat_id is the PK
            $table->foreign('depcat_id', 'fk_rdb_project_depcat_id')
                  ->references('depcat_id')
                  ->on('rdb_department_category')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_project', function (Blueprint $table) {
            $table->dropForeign('fk_rdb_project_depcat_id');
        });
    }
};
