<?php

namespace PdfFit\Core;

final class Logger
{
    private const RESET = "\033[0m";
    private const GREEN = "\033[32m";
    private const BLUE = "\033[34m";
    private const YELLOW = "\033[33m";
    private const MAGENTA = "\033[35m";

    public static function info(string $message): void
    {
        self::output(self::BLUE . '• ' . $message . self::RESET);
    }

    public static function success(string $message): void
    {
        self::output(self::GREEN . '✔ ' . $message . self::RESET);
    }

    public static function warn(string $message): void
    {
        self::output(self::YELLOW . '⚠ ' . $message . self::RESET);
    }

    public static function headline(string $title): void
    {
        self::output(self::MAGENTA . "\n== {$title} ==\n" . self::RESET);
    }

    public static function printSummary(array $result): void
    {
        self::headline('PDF Fit Summary');
        self::output('Input:  ' . $result['input']);
        self::output('Output: ' . $result['output']);
        self::output(sprintf('Size before: %.2f MB', $result['sizeBefore'] / 1024 / 1024));
        self::output(sprintf('Size after:  %.2f MB', $result['sizeAfter'] / 1024 / 1024));
        $gain = $result['sizeBefore'] > 0
            ? (1 - ($result['sizeAfter'] / $result['sizeBefore'])) * 100
            : 0;
        self::output(sprintf('Gain: %.1f%%', $gain));
        self::output('Strategy: ' . json_encode($result['strategy']));
        self::output('Duration: ' . sprintf('%.3fs', $result['duration']));

        if (!empty($result['notes'])) {
            self::headline('Processing Notes');
            foreach ($result['notes'] as $note) {
                self::output('- ' . $note);
            }
        }

        if (!empty($result['plugins'])) {
            self::headline('Plugin Outputs');
            foreach ($result['plugins'] as $name => $payload) {
                self::output($name . ': ' . json_encode($payload));
            }
        }
    }

    public static function printBatchSummary(array $results): void
    {
        self::headline('Batch Summary');
        foreach ($results as $file => $result) {
            self::output($file);
            self::output(sprintf('  → %.2f MB → %.2f MB', $result['sizeBefore'] / 1048576, $result['sizeAfter'] / 1048576));
        }
    }

    private static function output(string $message): void
    {
        fwrite(STDOUT, $message . "\n");
    }
}
