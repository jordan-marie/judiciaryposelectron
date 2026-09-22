<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Live Dashboard & 2-Stage Weighment
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/transactions/inbound', [DashboardController::class, 'storeInbound'])->name('transactions.inbound')->middleware('can:perform live transaction');
    Route::post('/transactions/outbound/{transaction}', [DashboardController::class, 'storeOutbound'])->name('transactions.outbound')->middleware('can:perform live transaction');

    // Transactions History & Filter Engine
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('can:view transaction');
    Route::get('/transactions/export', [TransactionController::class, 'exportCsv'])->name('transactions.export')->middleware('can:export reporting');

    // Dynamic Form Builder
    Route::get('/forms', [FormBuilderController::class, 'index'])->name('forms.index')->middleware('can:manage form builder');
    Route::post('/forms/{form}/fields', [FormBuilderController::class, 'createField'])->name('forms.fields.create')->middleware('can:manage form builder');
    Route::delete('/forms/fields/{field}', [FormBuilderController::class, 'deleteField'])->name('forms.fields.delete')->middleware('can:manage form builder');
});
