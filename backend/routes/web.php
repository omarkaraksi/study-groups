<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\StudyGroupAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);

// Locale switcher - accessible to both guests and authenticated users
Route::get('/admin/locale/{locale}', [LocaleController::class, 'switch'])->name('admin.locale.switch');

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::get('/admin/study-groups', [StudyGroupAdminController::class, 'index'])->name('admin.study-groups.index');
    Route::get('/admin/study-groups/{study_group}', [StudyGroupAdminController::class, 'show'])->name('admin.study-groups.show');
    Route::get('/admin/study-groups/{study_group}/edit', [StudyGroupAdminController::class, 'edit'])->name('admin.study-groups.edit');
    Route::patch('/admin/study-groups/{study_group}', [StudyGroupAdminController::class, 'update'])->name('admin.study-groups.update');
    Route::delete('/admin/study-groups/{study_group}', [StudyGroupAdminController::class, 'destroy'])->name('admin.study-groups.destroy');

    Route::get('/admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [UserAdminController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/admin/users/{user}', [UserAdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserAdminController::class, 'destroy'])->name('admin.users.destroy');
});
