<?php

namespace PdfFit;

use RuntimeException;

class PdfLoader
{
    public static function load(string $path): array
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Unable to read file: {$path}");
        }

        $binary = file_get_contents($path);
        if ($binary === false) {
            throw new RuntimeException("Failed to load file contents: {$path}");
        }

        return [
            'path'   => $path,
            'size'   => filesize($path) ?: 0,
            'binary' => $binary,
            'hash'   => sha1($binary),
        ];
    }
}
