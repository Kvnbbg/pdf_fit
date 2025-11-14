<?php

namespace PdfFit;

class PdfProcessor
{
    public static function process(array $pdf, array $strategy): array
    {
        return match ($strategy['type']) {
            'compress', 'optimize' => self::compress($pdf, $strategy),
            'resize'               => self::resize($pdf, $strategy),
            default                => ['binary' => $pdf['binary'], 'notes' => ['No processing executed.']],
        };
    }

    private static function compress(array $pdf, array $strategy): array
    {
        $ghostscript = self::ghostscriptBinary();
        if ($ghostscript === null) {
            Logger::warn('Ghostscript not found, returning original PDF.');
            return [
                'binary' => $pdf['binary'],
                'notes'  => ['Ghostscript unavailable, compression skipped.'],
            ];
        }

        $input = self::writeTempFile($pdf['binary'], '.pdf');
        $output = tempnam(sys_get_temp_dir(), 'pdf_fit_');
        $quality = $strategy['quality'] ?? 60;
        $dpi = $strategy['dpi'] ?? 150;

        $cmd = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/default ' .
            '-dDownsampleColorImages=true -dColorImageResolution=%d ' .
            '-dJPEGQ=%d -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
            escapeshellcmd($ghostscript),
            $dpi,
            $quality,
            escapeshellarg($output),
            escapeshellarg($input)
        );

        $shell = shell_exec($cmd);
        if (!is_string($shell)) {
            Logger::warn('Ghostscript failed, returning original PDF.');
            return [
                'binary' => $pdf['binary'],
                'notes'  => ['Ghostscript execution failed, original returned.'],
            ];
        }

        $compressed = file_get_contents($output);
        if ($compressed === false || $compressed === '') {
            Logger::warn('Compressed file empty, falling back to original.');
            return [
                'binary' => $pdf['binary'],
                'notes'  => ['Ghostscript produced empty output, original returned.'],
            ];
        }

        return [
            'binary' => $compressed,
            'notes'  => ['Ghostscript compression executed.'],
        ];
    }

    private static function resize(array $pdf, array $strategy): array
    {
        $ghostscript = self::ghostscriptBinary();
        if ($ghostscript === null) {
            throw new \RuntimeException('Resize mode requires Ghostscript to be installed.');
        }

        $width = max(320, (int) ($strategy['width'] ?? 1080));
        $height = max(320, (int) ($strategy['height'] ?? 1920));
        $fit = strtolower((string) ($strategy['fit'] ?? 'contain'));

        $input = self::writeTempFile($pdf['binary'], '.pdf');
        $output = tempnam(sys_get_temp_dir(), 'pdf_fit_');

        $command = [
            escapeshellcmd($ghostscript),
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-dFIXEDMEDIA',
            '-dDEVICEWIDTHPOINTS=' . $width,
            '-dDEVICEHEIGHTPOINTS=' . $height,
        ];

        if ($fit === 'contain') {
            $command[] = '-dPDFFitPage';
        }

        $command[] = '-sOutputFile=' . escapeshellarg($output);
        $command[] = escapeshellarg($input);

        $shell = shell_exec(implode(' ', $command) . ' 2>&1');

        @unlink($input);

        if (!is_string($shell)) {
            @unlink($output);
            throw new \RuntimeException('Ghostscript failed to execute resize command.');
        }

        $resized = @file_get_contents($output);
        @unlink($output);

        if ($resized === false || $resized === '') {
            throw new \RuntimeException('Ghostscript resize produced an empty result.');
        }

        $notes = [
            sprintf('Pages resized to %dx%d points using Ghostscript.', $width, $height),
            $fit === 'contain'
                ? 'Scaled to fit within target dimensions while preserving aspect ratio.'
                : 'Scaled to target dimensions with fixed media box.',
        ];

        return ['binary' => $resized, 'notes' => $notes];
    }

    private static function ghostscriptBinary(): ?string
    {
        $binary = trim((string) shell_exec('command -v gs'));
        return $binary !== '' ? $binary : null;
    }

    private static function writeTempFile(string $contents, string $suffix): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_fit_');
        $final = $path . $suffix;
        rename($path, $final);
        file_put_contents($final, $contents);

        return $final;
    }
}
