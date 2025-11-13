<?php

namespace PdfFit\Core;

use RuntimeException;

final class PdfLoader
{
    public static function load(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("File not found: {$path}");
        }

        $binary = file_get_contents($path);
        if ($binary === false) {
            throw new RuntimeException("Unable to read file: {$path}");
        }

        return [
            'path'     => realpath($path) ?: $path,
            'size'     => filesize($path) ?: strlen($binary),
            'mtime'    => filemtime($path) ?: time(),
            'binary'   => $binary,
            'checksum' => md5($binary),
        ];
    }
}
