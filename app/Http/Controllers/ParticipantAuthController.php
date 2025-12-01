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

    public function loginParticipant(Request $request)
    {
        $request->validate([
            'cc' => 'required|string',
        ]);

        $participant = Participant::firstOrCreate(
            ['cc' => $request->cc],
            ['name' => 'Participantetest ' . $request->cc]
        );

        // 3. Sincronización con Canary (Lazy Sync)
        // Si el participante no tiene ID de Canary guardado, lo creamos ahora
        if (empty($participant->canary_subject_id)) {
            try {
                $canaryId = $this->canaryService->createSubject(
                    $participant->cc,
                    $participant->name ?? ('Participante ' . $participant->cc)
                );

                // Guardamos el ID de Canary en tu DB
                $participant->update(['canary_subject_id' => $canaryId]);

            } catch (\Exception $e) {
                \Log::error("Error creando sujeto en Canary: " . $e->getMessage());
                return back()->withErrors(['msg' => 'Error de conexión con servicio de voz.']);
            }
        }

        Auth::guard('participant')->login($participant);
        $request->session()->regenerate();

        return redirect()->intended('/records/grabacion');
    }
}