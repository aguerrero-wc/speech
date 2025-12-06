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
        $this->surveyCode = env('CANARY_SURVEY_CODE');
    }


public function uploadAudioToCanary(string $preSignedUrl, string $audioFilePath)
    {
        $audioContent = file_get_contents($audioFilePath);
        
        $ch = curl_init($preSignedUrl);
        
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $audioContent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: audio/wav', // 🔥 Especificar WAV
                'Content-Length: ' . strlen($audioContent),
            ],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        \Log::info('Upload result', [
            'http_code' => $httpCode,
            'file_size' => strlen($audioContent)
        ]);
        
        if ($httpCode !== 200 && $httpCode !== 201 && $httpCode !== 204) {
            throw new Exception("Upload Audio Error: HTTP {$httpCode} - {$response}");
        }
        
        return true;
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
            'externalId' => $externalId,// Identificador en Cannay speech del participante
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

    public function getSubject(string $externalId)
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/subject";

        $response = Http::withToken($token)->get($url, [
            'id' => $externalId
        ]);

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

    public function listProjectSurveys()
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/reseller/project";

        $response = Http::withToken($token)->get($url, [
            'id' => $this->projectId
        ]);

        if ($response->failed()) {
            throw new Exception("Get Project Error: " . $response->body());
        }

        return $response->json();
    }

    //assessment -> grabaciones 
    public function beginAssessment(string $canarySubjectId)
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/assessment/begin";

        $body = [
            'subjectId' => $canarySubjectId,
            'surveyCode' => 'KALM_WELLNESS' ,
            'generateUploadUrls' => true,
            // 'metadata' => [
            //     'source' => 'web-app',
            //     'timestamp' => now()->toIso8601String()
            // ]
        ];

        $response = Http::withToken($token)->post($url, $body);

        if ($response->failed()) {
            throw new Exception("Begin Assessment Error: " . $response->body());
        }

        return $response->json();
    }

    public function endAssessment(string $assessmentId, array $responseData)
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/assessment/end";

        $body = [
            'assessmentId' => $assessmentId,
            'responseData' => $responseData
        ];

        $response = Http::withToken($token)->post($url, $body);

        if ($response->failed()) {
            throw new Exception("End Assessment Error: " . $response->body());
        }

        return $response->json();
    }


    public function getAssessmentScores(string $assessmentId)
    {
        $token = $this->getAccessToken();
        $url = "{$this->baseUrl}/v3/api/list-scores";

        $response = Http::withToken($token)->get($url, [
            'assessmentId' => $assessmentId,
            'includeErrors' => 'true'
        ]);

        if ($response->failed()) {
            throw new Exception("Get Scores Error: " . $response->body());
        }

        return $response->json();
    }
}