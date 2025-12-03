<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\CanaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantAuthController extends Controller
{
    protected $canaryService;

    public function __construct(CanaryService $canaryService)
    {
        $this->canaryService = $canaryService;
    }

    public function createParticipant(Request $request)
    {
        $request->validate([
            'cc' => 'required|string',
            'name' => 'required|string',
        ]);

        try {
            $canaryId = $this->canaryService->createSubject(
                $request->cc,
                $request->name
            );

            $participant = Participant::updateOrCreate(
                ['cc' => $request->cc],
                [
                    'name' => $request->name,
                    'canary_subject_id' => $canaryId
                ]
            );
        } catch (\Throwable $th) {
            \Log::error("Subject creation error: " . $th->getMessage());
            return response()->json([
                'status' => 'Error.',
                'message' => $th->getMessage()
            ], 500);
        }

    return response()->json([
        'status' => 'success',
        'message' => 'Subject created successfully.',
        'participant' => $participant,
        'canary_subject_id' => $canaryId
    ]);
    }
}