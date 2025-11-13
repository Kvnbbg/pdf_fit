<?php

namespace PdfFit;

class PdfExporter
{
    public static function export(array $processed, string $inputFile, array $strategy): string
    {
        $target = self::deriveOutputName($inputFile, $strategy['type'] ?? 'output');
        file_put_contents($target, $processed['binary']);

        return $target;
    }

    private static function deriveOutputName(string $input, string $suffix): string
    {
        $directory = dirname($input);
        $name = pathinfo($input, PATHINFO_FILENAME);
        $extension = pathinfo($input, PATHINFO_EXTENSION) ?: 'pdf';

        $candidate = sprintf('%s/%s_%s.%s', $directory, $name, $suffix, $extension);
        $counter = 1;
        while (file_exists($candidate)) {
            $candidate = sprintf('%s/%s_%s_%d.%s', $directory, $name, $suffix, $counter, $extension);
            $counter++;
        }

        return $candidate;
    }
}
