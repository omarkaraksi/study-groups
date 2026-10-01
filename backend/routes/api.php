<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StudyGroupController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user', fn (Request $request) => new UserResource($request->user()));
        Route::post('/study-groups', [StudyGroupController::class, 'store']);
        Route::patch('/study-groups/{study_group:slug}', [StudyGroupController::class, 'update']);
        Route::get('/study-groups/{study_group:slug}', [StudyGroupController::class, 'show']);
    });
});
