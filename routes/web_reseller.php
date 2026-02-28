<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reseller\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('reseller.dashboard');
