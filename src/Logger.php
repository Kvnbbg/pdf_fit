<?php

namespace PdfFit;

class Logger
{
    private const COLOR_INFO = "\033[36m";
    private const COLOR_WARN = "\033[33m";
    private const COLOR_RESET = "\033[0m";

    public static function info(string $message): void
    {
        self::line(self::COLOR_INFO . $message . self::COLOR_RESET);
    }

    public static function warn(string $message): void
    {
        self::line(self::COLOR_WARN . '⚠️  ' . $message . self::COLOR_RESET);
    }

    public static function line(string $message): void
    {
        fwrite(STDOUT, "• {$message}\n");
    }

    public static function printSummary(array $result): void
    {
        $gain = $result['sizeBefore'] > 0
            ? round(100 - ($result['sizeAfter'] / max($result['sizeBefore'], 1)) * 100, 2)
            : 0.0;

        $analysis = json_encode($result['analysis'], JSON_PRETTY_PRINT);
        $strategy = json_encode($result['strategy'], JSON_PRETTY_PRINT);

        $lines = [
            '============== SUMMARY ==============',
            "Input:   {$result['input']}",
            "Output:  {$result['output']}",
            'Size before: ' . self::formatSize($result['sizeBefore']),
            'Size after:  ' . self::formatSize($result['sizeAfter']),
            "Gain:        {$gain}%",
            "Duration:    {$result['duration']}s",
            '-- Analysis --',
            $analysis,
            '-- Strategy --',
            $strategy,
            '====================================='
        ];

        fwrite(STDOUT, "\n" . implode("\n", $lines) . "\n");
    }

    private static function formatSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return round($value, 2) . ' ' . $units[$power];
    }
}
