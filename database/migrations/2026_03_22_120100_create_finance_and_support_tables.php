<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('type', 30);
            $table->string('currency', 3)->default('XOF');
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('idempotency_key')->unique();
            $table->foreignId('debit_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('credit_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('amount_xof');
            $table->string('currency', 3)->default('XOF');
            $table->string('description');
            $table->nullableMorphs('related');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_assignment_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('base_amount_xof');
            $table->unsignedInteger('commission_xof');
            $table->unsignedTinyInteger('rate_percent')->default(10);
            $table->string('status', 30)->default('credited');
            $table->timestamps();

            $table->unique('task_assignment_id');
        });

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 40)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_xof');
            $table->unsignedInteger('fee_xof')->default(0);
            $table->unsignedInteger('net_amount_xof');
            $table->string('method', 30);
            $table->string('destination_phone', 30);
            $table->string('status', 30)->default('pending');
            $table->string('provider', 30)->nullable();
            $table->string('provider_transfer_id')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 40)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('category', 40)->default('general');
            $table->string('status', 30)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_staff')->default(false);
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('financial_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedInteger('amount_xof');
            $table->string('category', 40)->default('ops');
            $table->date('incurred_on');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_expenses');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_accounts');
    }
};
