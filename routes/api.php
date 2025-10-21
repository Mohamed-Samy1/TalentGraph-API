<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\CompanyController;

//  ACCOUNT ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgot']);
Route::post('/password/reset', [AuthController::class, 'reset']);

// PUBLIC ROUTES
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    
    // AUTH ROUTES
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    //  USER ROUTES
    Route::apiResource('users', UserController::class);

    // ROLE ROUTES
    Route::apiResource('roles', RoleController::class);

    //  VACANCIES Routes (except index and show which are public)
    Route::apiResource('vacancies', VacancyController::class)->except(['index', 'show']);

    //  COMPANIES Routes (except index which is public)
    Route::apiResource('companies', CompanyController::class)->except(['index']);
});