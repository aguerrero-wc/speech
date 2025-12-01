<?php

namespace App\Http\Controllers;

use App\Services\CanaryService;
use Illuminate\Http\JsonResponse;

class CanaryTestController extends Controller
{
    protected $canaryService;

    public function __construct(CanaryService $canaryService)
    {
        $this->canaryService = $canaryService;
    }

    public function ping(): JsonResponse
    {
        try {
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
