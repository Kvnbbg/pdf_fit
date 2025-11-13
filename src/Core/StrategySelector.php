<?php

namespace PdfFit\Core;

final class StrategySelector
{
    public static function select(string $mode, array $analysis, array $options = []): array
    {
        $mode = strtolower($mode);
        return match ($mode) {
            'smart'    => self::smartStrategy($analysis),
            'compress' => self::compressStrategy($options),
            'optimize' => self::optimizeStrategy($options),
            'extreme'  => ['type' => 'extreme'],
            'resize'   => self::resizeStrategy($options),
            default    => ['type' => $mode, 'notes' => ['Unknown mode, no operation executed.']],
        };
    }

    private static function smartStrategy(array $analysis): array
    {
        $config = Utils::readConfig();
        if ($analysis['isHuge']) {
            $profile = $config['smart']['huge'];
        } elseif ($analysis['isLarge']) {
            $profile = $config['smart']['large'];
        } elseif ($analysis['isMedium']) {
            $profile = $config['smart']['medium'];
        } else {
            $profile = $config['smart']['small'];
        }

        return [
            'type'    => 'compress',
            'quality' => $profile['quality'],
            'dpi'     => $profile['dpi'],
            'preset'  => 'smart',
        ];
    }

    private static function compressStrategy(array $options): array
    {
        return [
            'type'    => 'compress',
            'quality' => (int) ($options['quality'] ?? 60),
            'dpi'     => (int) ($options['dpi'] ?? 150),
            'preset'  => 'manual',
        ];
    }

    private static function optimizeStrategy(array $options): array
    {
        return [
            'type'    => 'compress',
            'quality' => (int) ($options['quality'] ?? 75),
            'dpi'     => (int) ($options['dpi'] ?? 220),
            'preset'  => 'optimize',
        ];
    }

    private static function resizeStrategy(array $options): array
    {
        return [
            'type'   => 'resize',
            'width'  => (int) ($options['width'] ?? 1080),
            'height' => (int) ($options['height'] ?? 1920),
            'preset' => $options['preset'] ?? 'viewport',
        ];
    }
}
