<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Payments\JekoWebhookController;
use App\Http\Controllers\Payments\SandboxPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('/webhooks/jeko', JekoWebhookController::class)->name('webhooks.jeko');

Route::middleware(['auth', 'account.active'])->prefix('payments/sandbox')->name('payments.sandbox.')->group(function () {
    Route::get('/{uuid}', [SandboxPaymentController::class, 'show'])->name('show');
    Route::post('/{uuid}', [SandboxPaymentController::class, 'complete'])->name('complete');
});

require __DIR__.'/worker.php';
require __DIR__.'/partner.php';
require __DIR__.'/admin.php';

