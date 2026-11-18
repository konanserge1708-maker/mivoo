<?php

use App\Http\Controllers\Partner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:partner'])
    ->prefix('partner')
    ->name('partner.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
