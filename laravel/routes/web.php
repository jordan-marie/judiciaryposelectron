<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScaleConsoleController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\QrCodeController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Logged-in Operators & Admins)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // QR Code Generator Route
    Route::get('qrcode-generator', [QrCodeController::class, 'index'])->name('qrcode.generator');

    // Scale Terminal Console Routes
    Route::get('scale', [ScaleConsoleController::class, 'index'])->name('scale.index');
    Route::get('scale/forms/{form}/fields', [ScaleConsoleController::class, 'getFormFields'])->name('scale.fields');
    Route::get('scale/transactions/{transaction}/data', [ScaleConsoleController::class, 'getTransactionData'])->name('scale.transaction-data');
    Route::post('scale/transactions', [ScaleConsoleController::class, 'storeTransaction'])->name('scale.store');

    // Transactions Log & Query Routes
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/export-csv', [TransactionController::class, 'exportCsv'])->name('transactions.export-csv');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('transactions/{transaction}/print', [TransactionController::class, 'printTicket'])->name('transactions.print');

    // Super Admin Routes
    Route::middleware(['role:Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dynamic Form Builder Routes
        Route::get('forms', [FormBuilderController::class, 'index'])->name('forms.index');
        Route::get('forms/create', [FormBuilderController::class, 'create'])->name('forms.create');
        Route::post('forms', [FormBuilderController::class, 'store'])->name('forms.store');
        Route::get('forms/{form}/edit', [FormBuilderController::class, 'edit'])->name('forms.edit');
        Route::put('forms/{form}', [FormBuilderController::class, 'update'])->name('forms.update');
        Route::post('forms/{form}/toggle-status', [FormBuilderController::class, 'toggleStatus'])->name('forms.toggle-status');
        Route::delete('forms/{form}', [FormBuilderController::class, 'destroy'])->name('forms.destroy');

        // Role & User Permission Routes
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'storeRole'])->name('roles.store');
        Route::post('roles/{role}/forms', [RoleController::class, 'updateRoleForms'])->name('roles.update-forms');
        Route::post('users/{user}/roles', [RoleController::class, 'updateUserRoles'])->name('users.update-roles');
    });
});
