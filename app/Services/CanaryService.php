<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CanaryService
{
    protected $baseUrl;
    protected $apiKey;
    protected $projectId; // <--- Agregamos esto, es obligatorio

    public function __construct()
    {
        $this->baseUrl = env('BASE_URL_CANARY'); // https://rest.eus.canaryspeech.com
        $this->apiKey = env('API_KEY_CANARY');
        $this->projectId = env('PROJECT_ID'); // Asegúrate de tener esto en el .env
    }

    /**
     * PASO 1: Obtener el Access Token (Login técnico)
     */
    private function getAccessToken()
    {
        $url = "{$this->baseUrl}/v3/auth/tokens/get";

        // Según tu doc: Header Csc-Api-Key
        $response = Http::withHeaders([
            'Csc-Api-Key' => $this->apiKey, 
            'Content-Type' => 'application/json'
        ])->post($url);

        if ($response->failed()) {
            // Usamos dd() aquí para ver por qué falla el login si ocurre
            dd('Error Auth:', $response->json());
        }

        return $response->json('accessToken');
    }

    /**
     * PASO 2: Crear el sujeto usando el Token
     */
    public function createTestSubject()
    {
        // 1. Nos autenticamos primero
        $token = $this->getAccessToken();

        // 2. Definimos URL
        $url = "{$this->baseUrl}/v3/api/create-subject";

        // 3. Body del Request (Es probable que pidan el Project ID aquí)
        $body = [
            'projectId' => $this->projectId, // Importante vincular al proyecto
            'name' => 'Laravel Test ' . time()
        ];

        // 4. Hacemos la petición con Bearer Token
        $response = Http::withToken($token) // Laravel pone el Header Authorization: Bearer ... automático
            ->post($url, $body);

        if ($response->failed()) {
            return [
                'status' => 'error',
                'code' => $response->status(),
                'message' => $response->json()
            ];
        }

        return [
            'status' => 'success',
            'code' => 201,
            'data' => $response->json()
        ];
    }
}