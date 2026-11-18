<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('tasks_completed_total')->default(0);
            $table->unsignedInteger('tasks_completed_today')->default(0);
            $table->date('tasks_counter_date')->nullable();
            $table->unsignedInteger('daily_earnings_xof')->default(0);
            $table->unsignedTinyInteger('quality_score')->default(100);
            $table->json('qualifications')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('CI');
            $table->timestamps();
        });

        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('company_registration')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('CI');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
        Schema::dropIfExists('worker_profiles');
    }
};
