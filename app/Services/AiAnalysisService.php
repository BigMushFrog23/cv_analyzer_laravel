<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private string $apiKey;

    public function __construct()
    {
        // Récupération de la clé via le config pour la stabilité
        $this->apiKey = config('services.gemini.key') ?? '';
    }

    public function analyze(string $cvText, string $jobTitle, string $jobDescription): array
    {
        $result = ['error' => 'Unknown error occurred'];

        if (empty($this->apiKey)) {
            return ['error' => 'API Key missing'];
        }

        $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription);

        try {
            // Modèle corrigé en 1.5-flash (le plus stable pour le free tier)
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

            // Timeout augmenté à 100s car le prompt est très long à traiter
            $response = Http::timeout(100)
                ->withoutVerifying()
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                        'temperature' => 0.1, // Stabilité maximale des scores
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', ['body' => $response->body()]);
                $result = ['error' => 'API Error: ' . ($response->json('error.message') ?? 'Unknown')];
            } else {
                $text = $response->json('candidates.0.content.parts.0.text', '');
                $feedback = $this->parseJsonResponse($text);
                
                // On s'assure que le résultat est bien wrappé dans 'feedback' pour le Controller
                $result = $feedback ? ['feedback' => $feedback] : ['error' => 'Invalid JSON from AI.'];
            }
        } catch (\Exception $e) {
            Log::error('System Error in AiAnalysisService: ' . $e->getMessage());
            $result = ['error' => 'System Error: ' . $e->getMessage()];
        }

        return $result;
    }

   private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription): string
    {
        return "Tu es un expert en recrutement spécialisé en systèmes ATS. 
        Analyse ce CV par rapport à l'offre de '$jobTitle'. 
        
        Tu dois impérativement répondre au format JSON strict suivant, sans aucun texte avant ou après.
        Chaque score est sur 100.
        
        {
        \"overallScore\": 0,
        \"ATS\": { \"score\": 0, \"tips\": [{\"type\": \"improve\", \"tip\": \"conseil\"}] },
        \"toneAndStyle\": { \"score\": 0, \"tips\": [{\"type\": \"improve\", \"tip\": \"conseil\"}] },
        \"content\": { \"score\": 0, \"tips\": [{\"type\": \"improve\", \"tip\": \"conseil\"}] },
        \"structure\": { \"score\": 0, \"tips\": [{\"type\": \"improve\", \"tip\": \"conseil\"}] },
        \"skills\": { \"score\": 0, \"tips\": [{\"type\": \"improve\", \"tip\": \"conseil\"}] },
        \"summary\": \"Ton résumé global ici...\"
        }

        Offre : $jobDescription
        CV à analyser : $cvText";
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
