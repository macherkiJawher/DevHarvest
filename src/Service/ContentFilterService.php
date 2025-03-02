<?php

namespace App\Service;

class ContentFilterService
{
    private array $inappropriateWords = [
        'idiot', 'stupide', 'crétin', 'connard', 'salopard', 'putain', 'merde',
        'fuck', 'shit', 'asshole', 'bitch', 'bastard',
    ];

    private array $agricultureKeywords = [
        'agriculture', 'ferme', 'récolte', 'culture', 'semence', 'engrais', 'sol', 'eau', 'plante',
        'tomate', 'blé', 'maïs', 'vache', 'poule', 'tracteur', 'irrigation', 'pesticide', 'panne', 
    ];

    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function filterContent(string $content, bool $useApi = false): array
    {
        $contentLower = strtolower($content);
        $words = preg_split('/\s+/', $contentLower, -1, PREG_SPLIT_NO_EMPTY);

        $isInappropriate = false;
        foreach ($this->inappropriateWords as $badWord) {
            if (stripos($contentLower, $badWord) !== false) {
                $isInappropriate = true;
                break;
            }
        }

        $isOffTopic = true;
        foreach ($this->agricultureKeywords as $keyword) {
            if (stripos($contentLower, $keyword) !== false) {
                $isOffTopic = false;
                break;
            }
        }

        if ($useApi && !$isInappropriate && !$isOffTopic) {
            try {
                $geminiResult = $this->geminiService->filterContent($content);
                return [
                    'isInappropriate' => $isInappropriate || $geminiResult['isInappropriate'],
                    'isOffTopic' => $isOffTopic || $geminiResult['isOffTopic']
                ];
            } catch (\Exception $e) {
                return [
                    'isInappropriate' => $isInappropriate,
                    'isOffTopic' => $isOffTopic
                ];
            }
        }

        return [
            'isInappropriate' => $isInappropriate,
            'isOffTopic' => $isOffTopic
        ];
    }
}