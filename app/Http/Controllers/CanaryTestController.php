<?php

namespace App\Http\Controllers;

use App\Services\CanaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Record;

class CanaryTestController extends Controller
{
    protected $canaryService;

    public function __construct(CanaryService $canaryService)
    {
        $this->canaryService = $canaryService;
    }

    public function getSubject(string $externalId): JsonResponse
    {
        try {
            $result = $this->canaryService->getSubject($externalId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'critical_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listProjectSurveys(): JsonResponse
    {
        try {
            $result = $this->canaryService->listProjectSurveys();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'critical_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function beginAssessment(string $participant_id): JsonResponse
    {
        try {
            $participant = Participant::findOrFail($participant_id);
            
            if (!$participant->canary_subject_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Participant not registered in Canary'
                ], 400);
            }
            
            $result = $this->canaryService->beginAssessment(
                $participant->canary_subject_id
            );

            $record = Record::create([
                'participant_id' => $participant_id,
                'canary_assessment_id' => $result['id'],
                'survey_type' => 'wellness',
                'status' => 'created',
                'duration' => 0,
                'audio_url' => null,
                'audio_format' => null,
                'upload_urls' => null,
                'response_data' => null,
                'scores' => null,
                'error_message' => null,
                'started_at' => null,
                'uploaded_at' => null,
                'processed_at' => null
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Participant not found'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Begin Assessment Error', [
                'participant_id' => $participant_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function processAudio(Request $request, string $participant_id): JsonResponse
    {
        try {
            $participant = Participant::findOrFail($participant_id);
            
            $assessment = $this->canaryService->beginAssessment(
                $participant->canary_subject_id
            );
            
            $assessmentId = $assessment['id'];
            $uploadUrls = $assessment['uploadUrls'];
            
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Descargar WebM
            $audioUrl = "https://pub-9a3693cc68ac458aaefcba5f0c73691f.r2.dev/records/2025-11-28%2F1764352667750_recording_1764352666479.webm";
            $webmPath = $tempDir . '/' . uniqid() . '.webm';
            
            $audioContent = file_get_contents($audioUrl);
            file_put_contents($webmPath, $audioContent);
            
            // 🔥 CONVERTIR A WAV
            $wavPath = $tempDir . '/' . uniqid() . '.wav';
            
            $command = sprintf(
                'ffmpeg -i %s -acodec pcm_s16le -ar 16000 -ac 1 %s 2>&1',
                escapeshellarg($webmPath),
                escapeshellarg($wavPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                \Log::error('FFmpeg failed', [
                    'command' => $command,
                    'output' => implode("\n", $output)
                ]);
                throw new Exception('Audio conversion failed');
            }
            
            \Log::info('WAV created', [
                'size' => filesize($wavPath),
                'original_size' => filesize($webmPath)
            ]);
            
            // Subir el WAV en lugar del WebM
            $uploadUrl = $uploadUrls['free_speech'];
            $this->canaryService->uploadAudioToCanary($uploadUrl, $wavPath);
            
            // Response data
            $duration = $this->getWavDuration($wavPath);
            
            $responseData = [
                [
                    'timestamp' => now()->toIso8601String(),
                    'type' => 'recordedResponse',
                    'code' => 'free_speech',
                    'data' => [
                        'filename' => basename($wavPath),
                        'duration' => $duration
                    ]
                ]
            ];
            
            // End assessment
            $result = $this->canaryService->endAssessment($assessmentId, $responseData);
            
            // Limpiar archivos temporales
            @unlink($webmPath);
            @unlink($wavPath);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Audio processed successfully',
                'assessment_id' => $assessmentId,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Process Audio Error', [
                'participant_id' => $participant_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getWavDuration(string $filePath): int
    {
        // Usa FFprobe para obtener duración exacta
        $command = sprintf(
            'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
            escapeshellarg($filePath)
        );
        
        $duration = trim(shell_exec($command));
        return (int) ceil((float) $duration);
    }

    private function getAudioDuration(string $filePath): int
    {
        // Puedes usar ffmpeg o getID3 para obtener la duración
        // Por ahora, un placeholder:
        return 60; // segundos
    }

    public function getAssessmentResults(string $assessmentId): JsonResponse
    {
        try {
            $result = $this->canaryService->getAssessmentScores($assessmentId);
            
            // Si el assessment ya tiene scores, actualiza el record
            if (isset($result['scores'])) {
                $record = Record::where('canary_assessment_id', $assessmentId)->first();
                
                if ($record) {
                    $record->update([
                        'status' => 'completed',
                        'scores' => $result['scores'],
                        'processed_at' => now()
                    ]);
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
