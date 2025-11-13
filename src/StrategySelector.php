<?php

namespace PdfFit;

class StrategySelector
{
    public static function select(string $mode, array $analysis, array $options): array
    {
        return match ($mode) {
            'smart'    => self::smartStrategy($analysis),
            'compress' => self::compressStrategy($options, $analysis),
            'resize'   => self::resizeStrategy($options),
            default    => ['type' => 'none'],
        };
    }

    private static function smartStrategy(array $analysis): array
    {
        if ($analysis['isHuge']) {
            return [
                'type'    => 'compress',
                'quality' => 30,
                'dpi'     => 110,
                'profile' => 'aggressive',
            ];
        }

        if ($analysis['isLarge']) {
            return [
                'type'    => 'compress',
                'quality' => 45,
                'dpi'     => 140,
                'profile' => 'balanced',
            ];
        }

        if ($analysis['isCompact']) {
            return [
                'type'    => 'optimize',
                'quality' => 70,
                'dpi'     => 200,
                'profile' => 'light-touch',
            ];
        }

        return [
            'type'    => 'compress',
            'quality' => 55,
            'dpi'     => 180,
            'profile' => 'standard',
        ];
    }

    private static function compressStrategy(array $options, array $analysis): array
    {
        $quality = isset($options['quality']) ? (int) $options['quality'] : ($analysis['isHuge'] ? 35 : 55);
        $dpi = isset($options['dpi']) ? (int) $options['dpi'] : 150;

        return [
            'type'    => 'compress',
            'quality' => max(10, min($quality, 95)),
            'dpi'     => max(72, min($dpi, 300)),
            'profile' => 'manual',
        ];
    }

    private static function resizeStrategy(array $options): array
    {
        $width = isset($options['width']) ? (int) $options['width'] : 1080;
        $height = isset($options['height']) ? (int) $options['height'] : 1920;

        return [
            'type'   => 'resize',
            'width'  => max(320, $width),
            'height' => max(320, $height),
            'fit'    => $options['fit'] ?? 'contain',
        ];
    }
}
