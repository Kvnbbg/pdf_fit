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
        $notes = [
            'Resize mode requested but not implemented. Returning original binary.',
            'Integrate imagick/ghostscript pipeline for full support.',
        ];

        return ['binary' => $pdf['binary'], 'notes' => $notes];
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
