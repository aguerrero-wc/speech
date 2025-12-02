<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanaryTestController;
use App\Http\Controllers\ParticipantAuthController;
use Illuminate\Session\Middleware\StartSession;

// Ruta de prueba simple
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/canary/ping', [CanaryTestController::class, 'ping']);
Route::get('/canary/subjects', [CanaryTestController::class, 'getSubjects']);
Route::post('/participant/create', [ParticipantAuthController::class, 'createParticipant']);