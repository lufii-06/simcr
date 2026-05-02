<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DeveloperStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
});

// Protected Routes (Auth)
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::redirect('/', '/dashboard');

    Route::get('/dashboard', function () {
        return view('pages.dashboard-overview');
    })->name('dashboard');

    Route::resource('user', UserController::class);
    Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
    Route::get('user/avatar/{filename}', [UserController::class, 'serveAvatar'])->name('user.avatar');
    Route::resource('client', ClientController::class);
    Route::resource('developer', DeveloperController::class);
    Route::get('project/search', [ProjectController::class, 'search'])->name('project.search');
    Route::resource('project', ProjectController::class);

    Route::prefix('setting')->group(function () {
        Route::resource('developer-status', DeveloperStatusController::class);
        Route::resource('project-status', ProjectStatusController::class);
        Route::resource('specialization', \App\Http\Controllers\SpecializationController::class);
    });

    Route::resource('repository', RepositoryController::class)->only(['index', 'show']);
    Route::post('repository/{repository}/toggle-visibility', [RepositoryController::class, 'toggleVisibility'])->name('repository.toggle-visibility');
    Route::post('repository/{repository}/toggle-status', [RepositoryController::class, 'toggleStatus'])->name('repository.toggle-status');
    Route::post('repository/{repository}/generate-token', [RepositoryController::class, 'generateToken'])->name('repository.generate-token');
    Route::get('repository/{repository}/view-file', [RepositoryController::class, 'viewFile'])->name('repository.view-file');
    Route::get('repository/{repository}/download-file', [RepositoryController::class, 'downloadFile'])->name('repository.download-file');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
