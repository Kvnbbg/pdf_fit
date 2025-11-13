<?php

namespace PdfFit\Core;

final class Utils
{
    public static function commandExists(string $command): bool
    {
        $result = trim((string) shell_exec(sprintf('command -v %s 2>/dev/null', escapeshellcmd($command))));
        return $result !== '';
    }

    public static function tempFile(string $suffix = ''): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_fit_');
        if ($suffix !== '') {
            $newPath = $path . $suffix;
            rename($path, $newPath);
            $path = $newPath;
        }

        return $path;
    }

    public static function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }

    public static function readConfig(): array
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $configFile = __DIR__ . '/../../config.php';
        if (file_exists($configFile)) {
            $loaded = include $configFile;
            if (is_array($loaded)) {
                $config = $loaded;
                return $config;
            }
        }

        $config = [
            'smart' => [
                'huge'   => ['quality' => 30, 'dpi' => 120],
                'large'  => ['quality' => 45, 'dpi' => 150],
                'medium' => ['quality' => 60, 'dpi' => 180],
                'small'  => ['quality' => 75, 'dpi' => 220],
            ],
            'paths' => [
                'output_suffix' => 'pdf_fit',
            ],
            'plugins' => [
                'thumbnail_quality' => 85,
            ],
        ];

        return $config;
    }
}
