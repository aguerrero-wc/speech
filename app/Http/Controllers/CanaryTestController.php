<?php

namespace App\Http\Controllers;

use App\Services\CanaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
