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
            return ['error' => 'API Key missing'];
        }

        $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription, $yearsExp);

        try {
            /**
             * We use v1beta as per your AI Studio export.
             * We use 'generateContent' instead of 'streamGenerateContent' for a simple JSON response.
             */
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
                return ['error' => 'API Error: ' . ($response->json('error.message') ?? 'Unknown')];
            }

            // In thinking models, the text is still in the same place
            $text = $response->json('candidates.0.content.parts.0.text', '');

            if (empty($text)) {
                return ['error' => 'Empty response from AI.'];
            }

            $feedback = $this->parseJsonResponse($text);

            return $feedback ? ['feedback' => $feedback] : ['error' => 'Invalid JSON from AI.'];

        } catch (\Exception $e) {
            return ['error' => 'System Error: ' . $e->getMessage()];
        }
    }

    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription, int $yearsExp): string
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