<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('onboarding_status', 20)->default('approved')->after('status');
            $table->unsignedInteger('onboarding_department_id')->nullable()->after('onboarding_status');
            $table->unsignedInteger('onboarding_prefix_id')->nullable()->after('onboarding_department_id');
            $table->string('onboarding_fname', 100)->nullable()->after('onboarding_prefix_id');
            $table->string('onboarding_lname', 100)->nullable()->after('onboarding_fname');
            $table->string('onboarding_fname_en', 100)->nullable()->after('onboarding_lname');
            $table->string('onboarding_lname_en', 100)->nullable()->after('onboarding_fname_en');
            $table->string('onboarding_tel', 50)->nullable()->after('onboarding_lname_en');
            $table->string('onboarding_position', 100)->nullable()->after('onboarding_tel');
            $table->timestamp('onboarding_submitted_at')->nullable()->after('onboarding_position');
            $table->timestamp('onboarding_approved_at')->nullable()->after('onboarding_submitted_at');
            $table->unsignedBigInteger('onboarding_approved_by')->nullable()->after('onboarding_approved_at');
            $table->text('onboarding_reject_reason')->nullable()->after('onboarding_approved_by');
        });

        // Set existing users without researcher_id to 'none'
        DB::table('user')
            ->whereNull('researcher_id')
            ->update(['onboarding_status' => 'none']);
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_status',
                'onboarding_department_id',
                'onboarding_prefix_id',
                'onboarding_fname',
                'onboarding_lname',
                'onboarding_fname_en',
                'onboarding_lname_en',
                'onboarding_tel',
                'onboarding_position',
                'onboarding_submitted_at',
                'onboarding_approved_at',
                'onboarding_approved_by',
                'onboarding_reject_reason',
            ]);
        });
    }
};
