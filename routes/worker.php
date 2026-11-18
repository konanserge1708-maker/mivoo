<?php

use App\Http\Controllers\Worker\DashboardController;
use App\Http\Controllers\Worker\ProfileController;
use App\Http\Controllers\Worker\ProgressController;
use App\Http\Controllers\Worker\ReferralController;
use App\Http\Controllers\Worker\SubscriptionController;
use App\Http\Controllers\Worker\SupportController;
use App\Http\Controllers\Worker\TaskController;
use App\Http\Controllers\Worker\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:worker'])
    ->prefix('worker')
    ->name('worker.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks/claim', [TaskController::class, 'claim'])->name('tasks.claim');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
        Route::get('/tasks/{task}/open', [TaskController::class, 'open'])->name('tasks.open');
        Route::post('/tasks/{task}/submit', [TaskController::class, 'submit'])->name('tasks.submit');
        Route::post('/tasks/{task}/dispute', [TaskController::class, 'dispute'])->name('tasks.dispute');

        Route::get('/progress', ProgressController::class)->name('progress');
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
        Route::get('/withdrawals', [WalletController::class, 'withdrawals'])->name('withdrawals');
        Route::get('/referrals', ReferralController::class)->name('referrals');
        Route::get('/profile', ProfileController::class)->name('profile');
        Route::view('/notifications', 'worker.notifications')->name('notifications');

        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::post('/support', [SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscription-history', [SubscriptionController::class, 'history'])->name('subscriptions.history');
        Route::get('/subscriptions/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
        Route::post('/subscriptions/{plan}', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/payment/{order}/success', [SubscriptionController::class, 'paymentSuccess'])->name('subscriptions.payment.success');
        Route::get('/subscriptions/payment/{order}/error', [SubscriptionController::class, 'paymentError'])->name('subscriptions.payment.error');
    });
