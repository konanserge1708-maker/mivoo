<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\TaskReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'account.active',
    'role:admin,super_admin,financial_manager,campaign_manager,support_agent',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/tasks', [TaskReviewController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskReviewController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/approve', [TaskReviewController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{task}/reject', [TaskReviewController::class, 'reject'])->name('tasks.reject');

        Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
        Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
        Route::post('/campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
        Route::post('/campaigns/{campaign}/suspend', [CampaignController::class, 'suspend'])->name('campaigns.suspend');
        Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
        Route::post('/users/{user}/withdrawals/block', [UserController::class, 'blockWithdrawals'])->name('users.withdrawals.block');
        Route::post('/users/{user}/withdrawals/unblock', [UserController::class, 'unblockWithdrawals'])->name('users.withdrawals.unblock');
        Route::post('/users/{user}/withdrawals/examination', [UserController::class, 'setWithdrawalsExamination'])->name('users.withdrawals.examination');
        Route::post('/users/{user}/withdrawals/limit', [UserController::class, 'updateWithdrawalLimit'])->name('users.withdrawals.limit');

        Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/examine', [WithdrawalController::class, 'examine'])->name('withdrawals.examine');
        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

        Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
        Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
        Route::post('/support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');

        Route::middleware('role:admin,super_admin')->group(function () {
            Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
            Route::get('/subscription-plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
            Route::put('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
