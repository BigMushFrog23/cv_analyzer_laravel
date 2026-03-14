<?php

namespace App\Services;

class PdfTextExtractor
{
    /**
     * Extraire le texte d'un fichier PDF
     */
    public function extract(string $filePath): string
    {
        // Méthode 1 : pdftotext (poppler-utils)
        $text = $this->tryPdfToText($filePath);
        if ($text && strlen(trim($text)) > 50) {
            return $this->sanitize($text);
        }

        // Méthode 2 : lecture brute du PDF
        $text = $this->parseRaw($filePath);
        return $this->sanitize($text);
    }

    private function tryPdfToText(string $filePath): string
    {
        if (!function_exists('shell_exec')) return '';
        $escaped = escapeshellarg($filePath);
        // 2>NUL supprime les erreurs sur Windows
        $output = @shell_exec("pdftotext {$escaped} - 2>NUL");
        return $output ?? '';
    }

    private function parseRaw(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if (!$content) return '';

        $text = '';

        // Extraire les blocs de texte PDF (entre BT et ET)
        if (preg_match_all('/BT(.*?)ET/s', $content, $btMatches)) {
            foreach ($btMatches[1] as $block) {
                if (preg_match_all('/\(([^)]+)\)/', $block, $strMatches)) {
                    $text .= implode(' ', $strMatches[1]) . ' ';
                }
            }
        }

        // Fallback : toutes les chaînes entre parenthèses lisibles
        if (strlen(trim($text)) < 50) {
            preg_match_all('/\(([^\)]{2,})\)/', $content, $matches);
            $candidates = array_filter($matches[1], function ($s) {
                return preg_match('/[a-zA-Z]{2,}/', $s)
                    && !preg_match('/[\x00-\x08\x0E-\x1F]{2,}/', $s);
            });
            $text = implode(' ', $candidates);
        }

        return preg_replace('/\s+/', ' ', trim($text));
    }

    private function sanitize(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        return trim($text);
    }
}
