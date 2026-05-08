<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::get('/my-reports', [ItemController::class, 'myReports'])->name('items.mine');
    Route::get('/resolved-reports', [ItemController::class, 'resolvedReports'])->name('items.resolved');
    Route::get('/archived-reports', [ItemController::class, 'archivedReports'])->name('items.archived');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');

    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item:slug}/confirmation', [ItemController::class, 'confirmation'])->name('items.confirmation');
    Route::get('/items/{item:slug}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item:slug}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item:slug}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item:slug}/status', [ItemController::class, 'updateStatus'])->name('items.status');

    Route::post('/items/{item:slug}/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/items/{item:slug}', [ItemController::class, 'show'])->name('items.show');

require __DIR__.'/auth.php';
