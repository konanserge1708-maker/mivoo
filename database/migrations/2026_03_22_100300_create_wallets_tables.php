<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('available_balance_xof')->default(0);
            $table->unsignedBigInteger('pending_balance_xof')->default(0);
            $table->unsignedBigInteger('lifetime_earned_xof')->default(0);
            $table->unsignedBigInteger('lifetime_withdrawn_xof')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->bigInteger('amount_xof');
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 30)->default('completed');
            $table->string('reference')->nullable();
            $table->string('idempotency_key')->unique();
            $table->nullableMorphs('related');
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
