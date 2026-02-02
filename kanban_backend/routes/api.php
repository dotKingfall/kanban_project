<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\KanbanColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Clients
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients', [ClientController::class, 'destroyMany']);
    Route::get('/reports/clients', [ClientController::class, 'reports']);

    // Demands
    Route::get('/demands', [DemandController::class, 'index']);
    Route::post('/demands', [DemandController::class, 'store']);
    Route::patch('/demands/{id}', [DemandController::class, 'update']);
    Route::patch('/demands/{id}/status', [DemandController::class, 'updateStatus']);
    Route::patch('/demands/{id}/position', [DemandController::class, 'updatePosition']);
    Route::delete('/demands', [DemandController::class, 'destroyMany']);

    // Kanban Columns
    Route::patch('/kanban_column', [KanbanColumnController::class, 'update']);
    Route::delete('/kanban_column', [KanbanColumnController::class, 'destroy']);
});
