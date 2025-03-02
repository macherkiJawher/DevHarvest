<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeminiService
{
    private string $apiKey;
    private Client $httpClient;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client();
    }

    /**
     * Génère une réponse automatique basée sur le contenu.
     */
    public function generateAutoResponse(string $content): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite-001:generateContent?key=" . $this->apiKey;

        $prompt = "Answer the provided content or give more details about the subject. Do not ask the user to ask anything else and answer in the language he speaks:\n\n" . $content;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->httpClient->post($url, [
                'json' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            return $responseBody['candidates'][0]['content']['parts'][0]['text'];
        } catch (RequestException $e) {
            throw new \Exception('Erreur lors de la génération de la réponse : ' . $e->getMessage());
        }
    }

    /**
     * Analyse le contenu pour détecter s'il est inapproprié ou hors sujet.
     */
    public function filterContent(string $content): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite-001:generateContent?key=" . $this->apiKey;

        $prompt = "Analyze the following content and determine if it is inappropriate (e.g., contains insults, offensive language, vulgarity, hate speech, or illegal content) or off-topic for an agricultural forum. Be strict and sensitive to potentially offensive or irrelevant material, but allow agricultural-related content. Respond only with a JSON object containing 'isInappropriate' (boolean) and 'isOffTopic' (boolean):\n\n" . $content;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->httpClient->post($url, [
                'json' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            $resultText = $responseBody['candidates'][0]['content']['parts'][0]['text'];
            $result = json_decode($resultText, true);

            // Fallback si le JSON est invalide
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Réponse Gemini invalide : ' . $resultText);
            }

            return [
                'isInappropriate' => $result['isInappropriate'] ?? false,
                'isOffTopic' => $result['isOffTopic'] ?? false
            ];
        } catch (RequestException $e) {
            throw new \Exception('Erreur lors du filtrage du contenu : ' . $e->getMessage());
        }
    }
}