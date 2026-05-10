<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DeveloperStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data & Settings (PM only)
    Route::middleware('role:pm')->group(function () {
        Route::resource('user', UserController::class);
        Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
        Route::resource('client', ClientController::class);
        Route::resource('developer', DeveloperController::class);

        Route::prefix('setting')->group(function () {
            Route::resource('developer-status', DeveloperStatusController::class);
            Route::resource('project-status', ProjectStatusController::class);
            Route::resource('task-status', TaskStatusController::class);
            Route::resource('specialization', SpecializationController::class);
        });
    });

    // Reports (PM & Owner only)
    Route::middleware('role:pm,owner')->group(function () {
        Route::prefix('report')->name('report.')->group(function () {
            Route::get('/', function() { return redirect()->route('report.master'); })->name('index');
            Route::get('/master', [ReportController::class, 'master'])->name('master');
            Route::get('/project', [ReportController::class, 'project'])->name('project');
            Route::get('/task', [ReportController::class, 'task'])->name('task');
            Route::get('/repository', [ReportController::class, 'repository'])->name('repository');
            
            // Export routes
            Route::post('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
            Route::post('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        });
    });

    Route::get('user/avatar/{filename}', [UserController::class, 'serveAvatar'])->name('user.avatar');

    // Projects
    Route::get('project/search', [ProjectController::class, 'search'])->name('project.search');
    Route::get('project/{project}/analytics', [ProjectController::class, 'analytics'])->name('project.analytics');
    
    // Restriction for Project Creation (PM & Leader only)
    Route::middleware('role:pm,leader')->group(function () {
        Route::get('project/create', [ProjectController::class, 'create'])->name('project.create');
        Route::post('project', [ProjectController::class, 'store'])->name('project.store');
        Route::get('project/{project}/edit', [ProjectController::class, 'edit'])->name('project.edit');
        Route::put('project/{project}', [ProjectController::class, 'update'])->name('project.update');
        Route::get('project/{project}', [ProjectController::class, 'show'])->name('project.show');
        Route::delete('project/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
    });

    Route::resource('project', ProjectController::class)->only(['index', 'show']);

    // Tasks
    Route::get('task', [TaskController::class, 'index'])->name('task.index');
    Route::get('task/log', [TaskController::class, 'log'])->name('task.log');
    Route::get('task/create', [TaskController::class, 'create'])->name('task.create');
    Route::post('task', [TaskController::class, 'store'])->name('task.store');
    Route::get('task/{task}', [TaskController::class, 'show'])->name('task.show');
    Route::get('project/{project}/users', [TaskController::class, 'getProjectUsers'])->name('project.users');
    Route::post('task/checklist/{checklist}/toggle', [TaskController::class, 'toggleChecklist'])->name('task.checklist.toggle');
    Route::post('task/{task}/status', [TaskController::class, 'updateStatus'])->name('task.update-status');

    Route::resource('repository', RepositoryController::class)->only(['index', 'show']);
    Route::post('repository/{repository}/toggle-visibility', [RepositoryController::class, 'toggleVisibility'])->name('repository.toggle-visibility');
    Route::post('repository/{repository}/toggle-status', [RepositoryController::class, 'toggleStatus'])->name('repository.toggle-status');
    Route::post('repository/{repository}/generate-token', [RepositoryController::class, 'generateToken'])->name('repository.generate-token');
    Route::get('repository/{repository}/view-file', [RepositoryController::class, 'viewFile'])->name('repository.view-file');
    Route::get('repository/{repository}/download-file', [RepositoryController::class, 'downloadFile'])->name('repository.download-file');
    Route::get('repository/{repository}/download-archive', [RepositoryController::class, 'downloadArchive'])->name('repository.download-archive');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Git HTTP Smart Protocol Routes (Accessible by Git CLI clients with token/auth) ini digunakan untuk cloning dan git action lainnya
Route::get('repository/{repositoryName}.git/info/refs', [RepositoryController::class, 'gitInfoRefs'])->name('repository.git-info-refs');
Route::post('repository/{repositoryName}.git/{service}', [RepositoryController::class, 'gitServiceRpc'])->where('service', 'git-upload-pack|git-receive-pack')->name('repository.git-service-rpc');