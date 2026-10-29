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
        // 1. rdb_review_template
        if (!Schema::hasTable('rdb_review_template')) {
            Schema::create('rdb_review_template', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('evaluation_mode', 50)->default('hybrid');
                $table->integer('scale_max')->default(5);
                $table->decimal('pass_threshold', 5, 2)->default(70.00);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // 2. rdb_review_criteria
        if (!Schema::hasTable('rdb_review_criteria')) {
            Schema::create('rdb_review_criteria', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('template_id')->index();
                $table->string('category', 150)->nullable();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->string('item_type', 50)->default('hybrid');
                $table->decimal('weight', 5, 2)->default(0);
                $table->boolean('is_required')->default(true);
                $table->integer('order_seq')->default(1);
                $table->timestamps();

                $table->foreign('template_id')
                    ->references('id')
                    ->on('rdb_review_template')
                    ->onDelete('cascade');
            });
        }

        // 3. rdb_review_submission
        if (!Schema::hasTable('rdb_review_submission')) {
            Schema::create('rdb_review_submission', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('pro_id')->index();
                $table->integer('round_number')->default(1);
                $table->string('submission_type', 50)->default('initial_draft');
                $table->string('submission_title', 255);
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('file_mime', 100)->nullable();
                $table->integer('page_count')->nullable();
                $table->text('note')->nullable();
                $table->integer('submitted_by')->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->string('status', 50)->default('submitted')->index();
                $table->text('admin_summary')->nullable();
                $table->integer('closed_by')->nullable();
                $table->dateTime('closed_at')->nullable();
                $table->integer('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. rdb_review_assignment
        if (!Schema::hasTable('rdb_review_assignment')) {
            Schema::create('rdb_review_assignment', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('submission_id')->index();
                $table->integer('reviewer_id')->index();
                $table->string('researcher_id', 50)->nullable()->index();
                $table->unsignedBigInteger('template_id')->index();
                $table->integer('assigned_by')->nullable();
                $table->dateTime('assigned_at')->nullable();
                $table->date('due_date')->nullable();
                $table->string('status', 50)->default('pending')->index();
                $table->text('decline_reason')->nullable();
                $table->dateTime('responded_at')->nullable();
                $table->string('result_decision', 50)->nullable();
                $table->decimal('total_score', 6, 2)->nullable();
                $table->decimal('max_possible_score', 6, 2)->nullable();
                $table->decimal('score_percentage', 5, 2)->nullable();
                $table->longText('general_comment')->nullable();
                $table->string('annotated_file_name', 255)->nullable();
                $table->string('annotated_file_path', 500)->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->timestamps();

                $table->foreign('submission_id')
                    ->references('id')
                    ->on('rdb_review_submission')
                    ->onDelete('cascade');

                $table->foreign('template_id')
                    ->references('id')
                    ->on('rdb_review_template');
            });
        }

        // 5. rdb_review_score
        if (!Schema::hasTable('rdb_review_score')) {
            Schema::create('rdb_review_score', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('assignment_id')->index();
                $table->unsignedBigInteger('criteria_id')->index();
                $table->decimal('score', 6, 2)->nullable();
                $table->string('pass_status', 30)->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->foreign('assignment_id')
                    ->references('id')
                    ->on('rdb_review_assignment')
                    ->onDelete('cascade');

                $table->foreign('criteria_id')
                    ->references('id')
                    ->on('rdb_review_criteria')
                    ->onDelete('cascade');
            });
        }

        // 6. rdb_review_clarification
        if (!Schema::hasTable('rdb_review_clarification')) {
            Schema::create('rdb_review_clarification', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('submission_id')->index();
                $table->integer('user_id')->index();
                $table->string('sender_role', 50);
                $table->string('action_type', 50)->default('clarification');
                $table->text('message');
                $table->string('attachment_name', 255)->nullable();
                $table->string('attachment_path', 500)->nullable();
                $table->timestamps();

                $table->foreign('submission_id')
                    ->references('id')
                    ->on('rdb_review_submission')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rdb_review_clarification');
        Schema::dropIfExists('rdb_review_score');
        Schema::dropIfExists('rdb_review_assignment');
        Schema::dropIfExists('rdb_review_submission');
        Schema::dropIfExists('rdb_review_criteria');
        Schema::dropIfExists('rdb_review_template');
    }
};
