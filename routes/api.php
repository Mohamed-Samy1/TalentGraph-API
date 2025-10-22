<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ApplicationController;

// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgot']);
Route::post('/password/reset', [AuthController::class, 'reset']);

Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    Route::apiResource('users', UserController::class);

    Route::apiResource('roles', RoleController::class);

    Route::apiResource('vacancies', VacancyController::class)->except(['index', 'show']);

    Route::apiResource('companies', CompanyController::class)->except(['index']);

    // Application routes
    Route::post('/apply/{vacancy}', [ApplicationController::class, 'apply']);
    Route::get('/applications/my-applications', [ApplicationController::class, 'myApplications']);
    Route::post('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);
});