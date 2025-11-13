<?php

namespace PdfFit\Plugins;

use PdfFit\Core\Logger;

final class PluginManager
{
    private static array $plugins = [];
    private static bool $booted = false;

    public static function bootDefaults(): void
    {
        if (self::$booted) {
            return;
        }

        self::register('compress_extreme', new CompressExtreme());
        self::register('high_quality', new HighQuality());
        self::register('metadata', new MetadataExtractor());
        self::register('thumbnail', new ThumbnailGenerator());

        self::$booted = true;
    }

    public static function register(string $name, callable $plugin): void
    {
        self::$plugins[$name] = $plugin;
    }

    public static function run(array $context): array
    {
        $results = [];
        foreach (self::$plugins as $name => $plugin) {
            try {
                $results[$name] = $plugin($context);
            } catch (\Throwable $exception) {
                Logger::warn("Plugin {$name} failed: " . $exception->getMessage());
                $results[$name] = ['error' => $exception->getMessage()];
            }
        }

        return $results;
    }
}
