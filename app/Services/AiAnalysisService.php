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

    public function analyze(string $cvText, string $jobTitle, string $jobDescription): array
    {
        // Initialize our single source of truth for the return
        $result = ['error' => 'Unknown error occurred'];

        // 1. Guard Clause (Checking API Key)
        if (empty($this->apiKey)) {
            return ['error' => 'API Key missing']; // Return #1
        }

        $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription);

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

            $response = Http::timeout(60)
                ->withoutVerifying()
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', ['body' => $response->body()]);
                $result = ['error' => 'API Error: ' . ($response->json('error.message') ?? 'Unknown')];
            } else {
                $text = $response->json('candidates.0.content.parts.0.text', '');

                if (empty($text)) {
                    $result = ['error' => 'Empty response from AI.'];
                } else {
                    $feedback = $this->parseJsonResponse($text);
                    $result = $feedback ? ['feedback' => $feedback] : ['error' => 'Invalid JSON from AI.'];
                }
            }
        } catch (\Exception $e) {
            $result = ['error' => 'System Error: ' . $e->getMessage()];
        }

        return $result; // Return #2
    }

    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription): string
    {
        return "Tu es un expert RH. Analyse ce CV pour le poste: {$jobTitle}.\n"
            . "IMPORTANT : Retourne UNIQUEMENT un objet JSON. Aucun autre texte.\n\n"
            . "CV: {$cvText}\n"
            . "OFFRE: {$jobDescription}\n"
            . "FORMAT ATTENDU: " . '{"overallScore":85,"ATS":{"score":80,"tips":[{"type":"improve","tip":"..."}]},"toneAndStyle":{"score":90,"tips":[]},"content":{"score":70,"tips":[]},"structure":{"score":80,"tips":[]},"skills":{"score":85,"tips":[]},"summary":"..."}';
    }

    private function parseJsonResponse(string $text): ?array
    {
        $text = trim($text);
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
