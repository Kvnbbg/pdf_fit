<?php

namespace PdfFit\Core;

final class PdfProcessor
{
    public static function process(array $pdf, array $strategy): array
    {
        $ghostscript = Utils::commandExists('gs');
        $context = [
            'binary'   => $pdf['binary'],
            'strategy' => $strategy,
            'notes'    => [],
        ];

        if (!$ghostscript) {
            Logger::warn('Ghostscript not detected on PATH. Skipping PDF transformation.');
            $context['notes'][] = 'Ghostscript unavailable; original file returned.';
            return $context;
        }

        $input = Utils::tempFile('.pdf');
        $output = Utils::tempFile('.pdf');

        file_put_contents($input, $pdf['binary']);

        try {
            $command = self::buildCommand($strategy['type'] ?? 'none', $input, $output, $strategy);
            if ($command === null) {
                $context['notes'][] = 'No transformation required for this strategy.';
                return $context;
            }

            exec($command, $outputLines, $exitCode);
            $processed = is_file($output) ? file_get_contents($output) : false;

            if ($exitCode !== 0 || !is_string($processed) || $processed === '') {
                Logger::warn('Ghostscript command failed; falling back to original payload.');
                $context['notes'][] = 'Ghostscript command failed; original binary preserved.';
                $processed = $pdf['binary'];
            } else {
                $context['notes'][] = 'Ghostscript pipeline executed successfully.';
            }

            $context['binary'] = is_string($processed) ? $processed : $pdf['binary'];
            return $context;
        } finally {
            @unlink($input);
            @unlink($output);
        }
    }

    private static function buildCommand(string $type, string $input, string $output, array $strategy): ?string
    {
        return match ($type) {
            'compress' => self::buildCompressCommand($input, $output, $strategy),
            'resize'   => self::buildResizeCommand($input, $output, $strategy),
            'extreme'  => self::buildExtremeCommand($input, $output),
            default    => null,
        };
    }

    private static function buildCompressCommand(string $input, string $output, array $strategy): string
    {
        $quality = max(10, min(95, (int) ($strategy['quality'] ?? 60)));
        $dpi = max(72, min(300, (int) ($strategy['dpi'] ?? 150)));

        $preset = match (true) {
            $quality <= 35 => '/screen',
            $quality <= 55 => '/ebook',
            $quality <= 75 => '/printer',
            default        => '/prepress',
        };

        return sprintf(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=%s '
            . '-dDownsampleColorImages=true -dColorImageResolution=%d '
            . '-dColorImageDownsampleType=/Average -dJPEGQ=%d '
            . '-dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            $preset,
            $dpi,
            $quality,
            escapeshellarg($output),
            escapeshellarg($input)
        );
    }

    private static function buildResizeCommand(string $input, string $output, array $strategy): string
    {
        $width = max(480, (int) ($strategy['width'] ?? 1080));
        $height = max(640, (int) ($strategy['height'] ?? 1920));

        return sprintf(
            'gs -sDEVICE=pdfwrite -dDEVICEWIDTHPOINTS=%d -dDEVICEHEIGHTPOINTS=%d '
            . '-dFIXEDMEDIA -dPDFFitPage -dNOPAUSE -dQUIET -dBATCH '
            . '-sOutputFile=%s %s',
            $width,
            $height,
            escapeshellarg($output),
            escapeshellarg($input)
        );
    }

    private static function buildExtremeCommand(string $input, string $output): string
    {
        return sprintf(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.3 -dPDFSETTINGS=/screen '
            . '-dDownsampleColorImages=true -dColorImageResolution=72 '
            . '-dDetectDuplicateImages=true -dCompressFonts=true '
            . '-dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            escapeshellarg($output),
            escapeshellarg($input)
        );
    }
}
