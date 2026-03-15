<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = trim(env('GEMINI_API_KEY', ''));
    }

    public function analyze(string $cvText, string $jobTitle, string $jobDescription, int $yearsExp): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API Key is missing in .env'];
        }

        $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription, $yearsExp);

        try {
            // UPDATED: Using the model name found in your listModels output
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

            $response = Http::timeout(60)
                ->withoutVerifying()
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                    // Keep generationConfig OUT to avoid payload errors
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', ['body' => $response->body()]);
                return ['error' => 'API Error: ' . ($response->json('error.message') ?? 'Connection failed')];
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');

            if (empty($text)) {
                return ['error' => 'IA returned empty response.'];
            }

            $feedback = $this->parseJsonResponse($text);

            if (!$feedback) {
                return ['error' => 'Invalid JSON from IA. Response was: ' . substr($text, 0, 100)];
            }

            return ['feedback' => $feedback];

        } catch (\Exception $e) {
            Log::error('AI Service Exception', ['msg' => $e->getMessage()]);
            return ['error' => 'System Error: ' . $e->getMessage()];
        }
    }

    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription, int $yearsExp): string
    {
        return "Tu es un expert RH. Analyse ce CV pour le poste: {$jobTitle}.\n"
            . "RETOURNE UNIQUEMENT DU JSON PUR. PAS DE TEXTE AVANT OU APRÈS.\n\n"
            . "CV: {$cvText}\n"
            . "OFFRE: {$jobDescription}\n\n"
            . "FORMAT JSON ATTENDU:\n"
            . '{"overallScore":85,"ATS":{"score":80,"tips":[{"type":"improve","tip":"Description","explanation":"Détail"}]},"toneAndStyle":{"score":90,"tips":[]},"content":{"score":70,"tips":[]},"structure":{"score":80,"tips":[]},"skills":{"score":85,"tips":[]},"summary":"Résumé."}';
    }

    private function parseJsonResponse(string $text): ?array
    {
        $text = trim($text);
        // Strip markdown backticks
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        
        if ($start !== false && $end !== false) {
            $json = substr($text, $start, $end - $start + 1);
            return json_decode($json, true);
        }

        return json_decode($text, true);
    }
}