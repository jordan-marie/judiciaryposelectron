<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\RoleController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated System Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Weighbridge Transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->middleware('permission:view-transactions')->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->middleware('permission:create-transactions')->name('create');
        Route::post('/', [TransactionController::class, 'store'])->middleware('permission:create-transactions')->name('store');
        Route::get('/export/csv', [TransactionController::class, 'exportCsv'])->middleware('permission:export-transactions')->name('exportCsv');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->middleware('permission:view-transactions')->name('show');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->middleware('permission:manage-forms')->name('destroy');
    });

    // Dynamic Form Builder
    Route::prefix('admin/form-builder')->name('form-builder.')->middleware('permission:manage-forms')->group(function () {
        Route::get('/', [FormBuilderController::class, 'index'])->name('index');
        Route::get('/create', [FormBuilderController::class, 'create'])->name('create');
        Route::post('/', [FormBuilderController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [FormBuilderController::class, 'edit'])->name('edit');
        Route::put('/{form}', [FormBuilderController::class, 'update'])->name('update');
        Route::post('/{form}/activate', [FormBuilderController::class, 'activate'])->name('activate');
        Route::delete('/{form}', [FormBuilderController::class, 'destroy'])->name('destroy');
    });

    // Roles & Permission Management
    Route::prefix('admin')->middleware('permission:manage-roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'storeRole'])->name('roles.storeRole');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'updateRolePermissions'])->name('roles.updateRolePermissions');
        Route::put('/users/{user}/roles', [RoleController::class, 'updateUserRoles'])->name('users.updateUserRoles');
    });
});
