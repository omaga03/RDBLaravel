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
        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            $table->text('utz_department_address')->nullable()->after('utz_department_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rdb_project_utilize', function (Blueprint $table) {
            $table->dropColumn('utz_department_address');
        });
    }
};
