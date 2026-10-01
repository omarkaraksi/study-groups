<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\StudyGroupAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);

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
});
