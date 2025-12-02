<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CanaryService
{
    protected $baseUrl;
    protected $apiKey;
    protected $projectId;

    public function __construct()
    {
        $this->baseUrl = env('BASE_URL_CANARY');
        $this->apiKey = env('API_KEY_CANARY');
        $this->projectId = env('PROJECT_ID');
    }

    /**
    **Obtener el Access Token
     **/
    private function getAccessToken()
    {
        $url = "{$this->baseUrl}/v3/auth/tokens/get";

        $response = Http::withHeaders([
            'Csc-Api-Key' => $this->apiKey, 
            'Content-Type' => 'application/json'
        ])->post($url);

        if ($response->failed()) {
            dd('Error Auth:', $response->json());
        }

        return $response->json('accessToken');
    }

    /**
     *Crear el sujeto usando el Token
     */
    public function createTestSubject()
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/create-subject";

        $body = [
            'projectId' => $this->projectId,
            'name' => 'Test ' . time()
        ];

        //Hacemos la petición con Bearer Token
        $response = Http::withToken($token) 
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

    public function createSubject(string $externalId, string $name)
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/create-subject";

        $body = [
            'projectId' => $this->projectId,
            'externalId' => $externalId,// ID participant
            'name' => $name
        ];

        $response = Http::withToken($token)->post($url, $body);

        if ($response->failed()) {
            // Lanzamos excepción para manejarla en el controller
            throw new Exception("Canary Error: " . $response->body());
        }

        // Retornamos solo el ID, que es lo que nos importa
        return $response->json('id');
    }

    public function getSubjects()
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/subjects";

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            return [
                'status' => 'error',
                'code' => $response->status(),
                'message' => $response->json()
            ];
        }

        return [
            'status' => 'success',
            'code' => 200,
            'data' => $response->json()
        ];
    }
}