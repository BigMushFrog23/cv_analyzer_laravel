<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class PdfTextExtractor
{
    public function extract(string $filePath): string
    {
        try {
            if (!file_exists($filePath)) {
                return '';
            }

            // Utilisation du parser pro
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            
            // Extraction globale
            $text = $pdf->getText();

            // Si getText() est capricieux (arrive sur certains exports Canva complexes)
            if (strlen(trim($text)) < 50) {
                $text = "";
                foreach ($pdf->getPages() as $page) {
                    $text .= $page->getText() . " ";
                }
            }

            return $this->sanitize($text);

        } catch (\Exception $e) {
            Log::error('Erreur extraction PDF: ' . $e->getMessage());
            
            // Fallback sur ta méthode shell si pdftotext est installé sur ton serveur
            return $this->tryPdfToText($filePath);
        }
    }

    private function tryPdfToText(string $filePath): string
    {
        if (!function_exists('shell_exec'))
        {
            return '';
        }
        $escaped = escapeshellarg($filePath);
        // Sur Windows c'est souvent pdftotext.exe, sur Linux pdftotext
        $output = @shell_exec("pdftotext {$escaped} - 2>NUL");
        return $output ? $this->sanitize($output) : '';
    }

    private function sanitize(string $text): string
    {
        // Nettoyage UTF-8 et caractères spéciaux
        if (!mb_check_encoding($text, 'UTF-8'))
        {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // Supprime les caractères de contrôle et normalise les espaces
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
}