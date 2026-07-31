<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ParentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parentUser}', [ParentController::class, 'show']);
    Route::patch('/parents/{parentUser}', [ParentController::class, 'update']);
    Route::delete('/parents/{parentUser}', [ParentController::class, 'destroy']);

    Route::get('/children', [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    Route::get('/children/{child}', [ChildController::class, 'show']);
    Route::patch('/children/{child}', [ChildController::class, 'update']);
    Route::delete('/children/{child}', [ChildController::class, 'destroy']);

    Route::get('/absences', [AbsenceController::class, 'index']);
    Route::post('/absences', [AbsenceController::class, 'store']);
    Route::get('/absences/{absence}', [AbsenceController::class, 'show']);
    Route::patch('/absences/{absence}', [AbsenceController::class, 'update']);
    Route::delete('/absences/{absence}', [AbsenceController::class, 'destroy']);
});
