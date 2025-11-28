<?php

namespace App\Http\Controllers;

use App\Services\CanaryService;
use Illuminate\Http\JsonResponse;

class CanaryTestController extends Controller
{
    protected $canaryService;

    // Inyección de Dependencias (Igual que en NestJS)
    public function __construct(CanaryService $canaryService)
    {
        $this->canaryService = $canaryService;
    }

    // public function ping(): JsonResponse
    // {
    //     try {
    //         $result = $this->canaryService->testConnection();
    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'critical_error',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function ping(): JsonResponse
    {
        try {
            // Cambiamos testConnection() por createTestSubject()
            $result = $this->canaryService->createTestSubject();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'critical_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
