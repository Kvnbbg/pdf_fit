<?php

namespace PdfFit\Core;

final class PdfExporter
{
    public static function export(array $processed, string $inputFile, array $strategy): string
    {
        $pathInfo = pathinfo($inputFile);
        $directory = $pathInfo['dirname'] ?? getcwd();
        $filename = $pathInfo['filename'] ?? 'output';
        $suffix = $strategy['type'] ?? 'processed';
        $timestamp = date('Ymd_His');
        $outputName = sprintf('%s_%s_%s.pdf', $filename, $suffix, $timestamp);
        $outputPath = $directory . DIRECTORY_SEPARATOR . $outputName;

        file_put_contents($outputPath, $processed['binary']);
        return $outputPath;
    }
}
