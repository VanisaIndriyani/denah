<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FloorPlanController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\RoomController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Floor Plans Routes
    Route::resource('floor-plans', FloorPlanController::class);
    Route::get('/floor-plans/{floorPlan}/print', [FloorPlanController::class, 'print'])->name('floor-plans.print');
    
    // Rooms Routes
    Route::resource('rooms', RoomController::class);
    
    // Points Routes (API-like)
    Route::post('/floor-plans/{floorPlan}/points', [PointController::class, 'store'])->name('points.store');
    Route::get('/floor-plans/{floorPlan}/rooms', [PointController::class, 'getRooms'])->name('points.get-rooms');
    Route::put('/points/{point}', [PointController::class, 'update'])->name('points.update');
    Route::delete('/points/{point}', [PointController::class, 'destroy'])->name('points.destroy');
});
