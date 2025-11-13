<?php

namespace PdfFit\Batch;

use PdfFit\Core\Pipeline;

final class BatchProcessor
{
    public static function runDirectory(string $directory, string $mode = 'smart', array $options = []): array
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Directory not found: {$directory}");
        }

        $results = [];
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.pdf') as $file) {
            $pipeline = new Pipeline($mode, $file, $options);
            $results[$file] = $pipeline->run();
        }

        return $results;
    }
}
