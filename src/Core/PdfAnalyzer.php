<?php

namespace PdfFit\Core;

final class PdfAnalyzer
{
    public static function analyze(array $pdf): array
    {
        $sizeMB = $pdf['size'] / 1048576;
        $content = $pdf['binary'];
        $imageMatches = self::countOccurrences($content, '/Subtype /Image');
        $fontMatches = self::countOccurrences($content, '/Font');

        $pageCount = self::detectPageCount($pdf['path']);
        $imageDensity = $pageCount > 0 ? $imageMatches / $pageCount : $imageMatches;

        return [
            'sizeMB'        => round($sizeMB, 2),
            'isHuge'        => $sizeMB >= 25,
            'isLarge'       => $sizeMB >= 10 && $sizeMB < 25,
            'isMedium'      => $sizeMB >= 2 && $sizeMB < 10,
            'isTiny'        => $sizeMB < 1,
            'imageObjects'  => $imageMatches,
            'fontObjects'   => $fontMatches,
            'imageDensity'  => round($imageDensity, 2),
            'pageCount'     => $pageCount,
            'hasTransparency' => str_contains($content, '/SMask'),
        ];
    }

    private static function countOccurrences(string $haystack, string $needle): int
    {
        return substr_count($haystack, $needle);
    }

    private static function detectPageCount(string $path): int
    {
        if (!Utils::commandExists('pdfinfo')) {
            return 0;
        }

        $output = shell_exec(sprintf('pdfinfo %s 2>/dev/null', escapeshellarg($path)));
        if (!is_string($output)) {
            return 0;
        }

        if (preg_match('/Pages:\s+(\d+)/', $output, $match)) {
            return (int) $match[1];
        }

        return 0;
    }
}
