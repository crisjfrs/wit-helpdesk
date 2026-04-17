<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Tickets
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('tickets/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    // Messages
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('tickets/{ticket}/messages', [MessageController::class, 'getMessages'])->name('messages.get');
    Route::post('messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::get('notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Profile
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Users (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
    });

    // Categories (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::post('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggleActive');
    });

    // Reports (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });
});
