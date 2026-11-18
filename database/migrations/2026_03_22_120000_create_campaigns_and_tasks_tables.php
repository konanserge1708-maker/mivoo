<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('partner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('campaign_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('instructions');
            $table->text('validation_criteria')->nullable();
            $table->string('evidence_type', 40)->default('text');
            $table->string('target_url')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('tasks_ordered');
            $table->unsignedInteger('tasks_assigned')->default(0);
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->unsignedInteger('tasks_approved')->default(0);
            $table->unsignedInteger('reward_per_task_xof');
            $table->unsignedInteger('budget_total_xof');
            $table->unsignedInteger('budget_reserved_xof')->default(0);
            $table->unsignedInteger('budget_spent_xof')->default(0);
            $table->unsignedInteger('budget_available_xof');
            $table->boolean('funding_confirmed')->default(false);
            $table->timestamp('funding_confirmed_at')->nullable();
            $table->unsignedTinyInteger('min_vip_level')->default(0);
            $table->unsignedInteger('max_participants')->nullable();
            $table->boolean('allow_repeat')->default(false);
            $table->unsignedSmallInteger('completion_minutes')->default(60);
            $table->string('country', 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'funding_confirmed']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('campaign_fundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('amount_xof');
            $table->string('method', 40)->default('admin_manual');
            $table->string('reference')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_budget_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_assignment_id')->nullable()->index();
            $table->unsignedInteger('amount_xof');
            $table->string('status', 30)->default('reserved');
            $table->string('purpose', 40)->default('task');
            $table->timestamps();
        });

        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('campaign_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('assigned');
            $table->unsignedInteger('reward_xof');
            $table->unsignedInteger('referral_reserve_xof')->default(0);
            $table->unsignedInteger('earned_xof')->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id']);
            $table->index(['user_id', 'status', 'assigned_at']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text_response')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->json('questionnaire_answers')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('task_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->string('status', 30)->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_disputes');
        Schema::dropIfExists('task_submissions');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('campaign_budget_reservations');
        Schema::dropIfExists('campaign_fundings');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('campaign_categories');
    }
};
