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

    public function ping(Request $request): JsonResponse
    {
        try {
            $result = $this->canaryService->createSubject($request->cc, $request->name);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'critical_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getSubjects(Request $request): JsonResponse
    {
        try {
            $result = $this->canaryService->getSubjects();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'critical_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
