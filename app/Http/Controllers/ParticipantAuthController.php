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

        $participant = Participant::firstOrCreate(
            ['cc' => $request->cc],
            ['name' => $request->name]
        );

        if (empty($participant->canary_subject_id)) {
            try {
                $canaryId = $this->canaryService->createSubject(
                $participant->cc,
                $participant->name
            );
            dd($canaryId);
            // $participant->update(['canary_subject_id' => $canaryId]);
        } catch (\Exception $e) {
            \Log::error("Error creando sujeto en Canary: " . $e->getMessage());
            return response()->json([
                'status' => 'Error de conexión con servicio de voz.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // CAMBIO: Usa token en lugar de sesión para API
    $token = $participant->createToken('api-token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Participante creado Canary',
        'token' => $token, // Devuelve el token
        'participant' => $participant,
    ]);
    }
}