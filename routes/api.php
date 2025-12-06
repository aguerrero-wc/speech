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

// end assessment
Route::post('/test/process-audio/{participant_id}', [CanaryTestController::class, 'processAudio']);
Route::get('/test/assessment/{assessmentId}/results', [CanaryTestController::class, 'getAssessmentResults']);

Route::get('/survey', [CanaryTestController::class, 'listProjectSurveys']);

Route::get('/canary/subjects/{externalId}', [CanaryTestController::class, 'getSubject']);
Route::post('/participant/create', [ParticipantAuthController::class, 'createParticipant']);
Route::post('/participants/{participantId}/assessment/begin', [CanaryTestController::class, 'beginAssessment']);