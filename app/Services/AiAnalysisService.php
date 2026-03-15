<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private string $apiKey;

    public function __construct()
    {
        // Use the config helper instead of env()
        $this->apiKey = config('services.gemini.key');
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
        return "Tu es un moteur d'audit ATS configuré sur un mode de précision binaire. 
        Analyse le CV pour le poste de '$jobTitle' en suivant cette matrice de 100 points. 
        Pour chaque point, c'est 0 ou le maximum, pas d'entre-deux.

        --- I. IDENTITÉ & ACCESSIBILITÉ (10 pts) ---
        1. Nom/Prénom présents (1pt)
        2. Numéro de téléphone valide (1pt)
        3. Email professionnel (1pt)
        4. Ville/Code Postal (1pt)
        5. Lien LinkedIn ou Portfolio (2pts)
        6. Titre du CV correspondant au métier (2pts)
        7. Photo absente ou professionnelle (1pt)
        8. Permis de conduire mentionné (si pertinent) (1pt)

        --- II. OPTIMISATION MOTS-CLÉS (25 pts) ---
        (Analyse l'OFFRE suivante : {$jobDescription})
        - 5 Mots-clés techniques de l'offre trouvés : (2pts par mot, total 10pts)
        - 5 Verbes d'action (Concevoir, Gérer, Analyser...) : (1pt par verbe, total 5pts)
        - Présence des outils/logiciels cités dans l'offre : (10pts)

        --- III. EXPÉRIENCE DÉTAILLÉE (25 pts) ---
        - Durée totale d'expérience > demande de l'offre (5pts)
        - Cohérence de la chronologie (pas de trou inexpliqué) (5pts)
        - Entreprises nommées clairement (2pts)
        - Missions détaillées pour chaque poste (5pts)
        - Évolution des responsabilités au fil du temps (3pts)
        - Alternance ou stages valorisés si débutant (5pts)

        --- IV. ANALYSE QUANTITATIVE (20 pts) ---
        - Présence de chiffres (ex: 20%, 50k€, 10 pers) : (5pts)
        - Indicateurs de performance (KPI) mentionnés : (5pts)
        - Dates de début/fin précises (Mois/Année) : (5pts)
        - Description des technos par projet : (5pts)

        --- V. FORMATION & COMPÉTENCES (10 pts) ---
        - Diplôme le plus élevé cité (2pts)
        - Nom de l'école/université présent (2pts)
        - Section 'Compétences' (Hard Skills) isolée (2pts)
        - Section 'Langues' avec niveau (A1, B2, C1...) (2pts)
        - Section 'Soft Skills' ou 'Loisirs' pertinente (2pts)

        --- VI. FORMATAGE TECHNIQUE (10 pts) ---
        - Usage de listes à puces (3pts)
        - Police lisible et uniforme (2pts)
        - Pas de logos/graphiques bloquant l'extraction (2pts)
        - Longueur optimale (1 à 2 pages max) (3pts)

        --- DONNÉES DE TEST ---
        CV : {$cvText}

        --- CONSIGNE DE SORTIE ---
        Réalise le calcul interne de chaque point. Le 'overallScore' doit être la somme mathématique exacte.
        Retourne UNIQUEMENT ce JSON :
        {
        \"overallScore\": 0,
        \"details\": {
            \"identite\": 0, \"keywords\": 0, \"experience\": 0, \"quantitatif\": 0, \"formation\": 0, \"formatage\": 0
        },
        \"feedback\": {
            \"overallScore\": 0,
            \"ATS\": { \"score\": 0, \"tips\": [] },
            \"summary\": \"...\"
        }
        }";
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
