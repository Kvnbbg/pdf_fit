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
            $context['notes'][] = 'Ghostscript unavailable, returning original binary.';
            return $context;
        }

        $input = Utils::tempFile('.pdf');
        file_put_contents($input, $pdf['binary']);
        $output = Utils::tempFile('.pdf');

        try {
            $type = $strategy['type'] ?? 'none';
            $command = match ($type) {
                'compress' => self::buildCompressCommand($input, $output, $strategy),
                'resize'   => self::buildResizeCommand($input, $output, $strategy),
                'extreme'  => self::buildExtremeCommand($input, $output),
                default    => null,
            };

            if ($command === null) {
                $context['notes'][] = 'No processing command generated.';
                return $context;
            }

            $result = shell_exec($command);
            if (!is_string($result)) {
                $context['notes'][] = 'Ghostscript execution returned no output, using original.';
                return $context;
            }

            $processed = file_get_contents($output);
            if (!is_string($processed) || $processed === '') {
                $context['notes'][] = 'Ghostscript output empty, using original.';
                return $context;
            }

            $context['binary'] = $processed;
            $context['notes'][] = 'Ghostscript pipeline executed successfully.';
            return $context;
        } finally {
            @unlink($input);
            @unlink($output);
        }
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
