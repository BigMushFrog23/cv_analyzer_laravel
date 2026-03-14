<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service : encapsule l'appel à l'API Anthropic (Claude)
 * Un Service = logique métier isolée, indépendante du contrôleur
 */
class AiAnalysisService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));
        $this->model  = config('services.anthropic.model', env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'));
    }

    /**
     * Analyser un CV par rapport à une offre d'emploi
     *
     * @return array ['feedback' => [...]] ou ['error' => '...']
     */
    public function analyze(string $cvText, string $jobTitle, string $jobDescription, int $yearsExp): array
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_API_KEY_HERE') {
            return ['error' => 'Clé API Anthropic non configurée dans le fichier .env'];
        }

        $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription, $yearsExp);

        try {
            // Laravel HTTP Client (wrapper de Guzzle — bien plus propre que curl)
            $response = Http::timeout(60)
                ->withoutVerifying()
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $this->model,
                    'max_tokens' => 2000,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['error' => 'Erreur API (HTTP ' . $response->status() . '): ' . $response->body()];
            }

            $text = $response->json('content.0.text', '');

            if (empty($text)) {
                return ['error' => 'Réponse IA vide.'];
            }

            $feedback = $this->parseJsonResponse($text);

            if (!$feedback) {
                return ['error' => 'Réponse IA non parseable: ' . substr($text, 0, 200)];
            }

            return ['feedback' => $feedback];

        } catch (\Exception $e) {
            Log::error('AI Service exception', ['message' => $e->getMessage()]);
            return ['error' => 'Erreur réseau: ' . $e->getMessage()];
        }
    }

    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription, int $yearsExp): string
    {
        // Nettoyer le texte pour éviter les problèmes d'encodage
        $cvText         = $this->sanitize($cvText);
        $jobTitle       = $this->sanitize($jobTitle);
        $jobDescription = $this->sanitize($jobDescription);

        return "Tu es un expert RH et recruteur senior. Analyse ce CV par rapport a l'offre d'emploi.\n"
            . "Retourne UNIQUEMENT un objet JSON valide, sans markdown, sans texte autour.\n\n"
            . "POSTE: {$jobTitle}\n"
            . "EXPERIENCE REQUISE: {$yearsExp} ans\n"
            . "DESCRIPTION: {$jobDescription}\n\n"
            . "CV:\n{$cvText}\n\n"
            . "Format JSON attendu (remplace les 0 par les vrais scores 0-100, donne 3-4 tips par section):\n"
            . '{"overallScore":0,'
            . '"ATS":{"score":0,"tips":[{"type":"good","tip":"exemple"}]},'
            . '"toneAndStyle":{"score":0,"tips":[{"type":"good","tip":"titre","explanation":"detail"}]},'
            . '"content":{"score":0,"tips":[{"type":"improve","tip":"titre","explanation":"detail"}]},'
            . '"structure":{"score":0,"tips":[{"type":"good","tip":"titre","explanation":"detail"}]},'
            . '"skills":{"score":0,"tips":[{"type":"improve","tip":"competence","explanation":"detail"}]},'
            . '"summary":"resume 2-3 phrases"}';
    }

    private function parseJsonResponse(string $text): ?array
    {
        $text = trim($text);
        // Enlever les balises markdown ```json ... ```
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // Extraire entre le premier { et le dernier }
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start !== false && $end !== false) {
            $json = substr($text, $start, $end - $start + 1);
            return json_decode($json, true);
        }

        return json_decode($text, true);
    }

    private function sanitize(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        if (strlen($text) > 12000) {
            $text = substr($text, 0, 12000) . '...[tronque]';
        }
        return $text;
    }
}
