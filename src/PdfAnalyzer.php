<?php

namespace PdfFit;

class PdfAnalyzer
{
    public static function analyze(array $pdf): array
    {
        $sizeBytes = $pdf['size'];
        $sizeMB = $sizeBytes / 1024 / 1024;
        $pageCount = self::detectPageCount($pdf['path']);

        $profile = [
            'sizeMB'    => round($sizeMB, 2),
            'pages'     => $pageCount,
            'density'   => $pageCount > 0 ? round($sizeMB / max($pageCount, 1), 3) : null,
            'isHuge'    => $sizeMB >= 15,
            'isLarge'   => $sizeMB >= 5 && $sizeMB < 15,
            'isCompact' => $sizeMB < 2,
        ];

        $profile['notes'] = self::buildNotes($profile);

        return $profile;
    }

    private static function detectPageCount(string $path): int
    {
        $binary = trim((string) shell_exec('command -v pdfinfo'));
        if ($binary === '') {
            return 0;
        }

        $escaped = escapeshellarg($path);
        $output = shell_exec("{$binary} {$escaped} 2>/dev/null");
        if (!is_string($output)) {
            return 0;
        }

        foreach (explode("\n", $output) as $line) {
            if (str_starts_with($line, 'Pages:')) {
                $parts = preg_split('/\s+/', trim($line));
                $value = end($parts);
                return is_numeric($value) ? (int) $value : 0;
            }
        }

        return 0;
    }

    private static function buildNotes(array $profile): array
    {
        $notes = [];
        if ($profile['isHuge']) {
            $notes[] = 'Very large PDF detected, aggressive compression recommended.';
        } elseif ($profile['isLarge']) {
            $notes[] = 'Large PDF detected, balanced compression suggested.';
        } elseif ($profile['isCompact']) {
            $notes[] = 'Already compact, prefer light optimization to preserve quality.';
        }

        if ($profile['pages'] === 0) {
            $notes[] = 'Page count unavailable (pdfinfo missing).';
        }

        return $notes;
    }
}
