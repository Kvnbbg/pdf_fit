<?php

namespace PdfFit\Plugins;

use PdfFit\Core\Utils;

final class ThumbnailGenerator
{
    public function __invoke(array $context): array
    {
        if (!Utils::commandExists('gs')) {
            return ['status' => 'skipped', 'reason' => 'Ghostscript not available'];
        }

        $outputImage = preg_replace('/\.pdf$/i', '_thumb.jpg', $context['output']);
        if (!$outputImage) {
            $outputImage = $context['output'] . '_thumb.jpg';
        }

        $config = Utils::readConfig();
        $pluginsConfig = $config['plugins'] ?? [];
        $quality = $pluginsConfig['thumbnail_quality'] ?? 85;

        $command = sprintf(
            'gs -dNOPAUSE -dBATCH -sDEVICE=jpeg -dJPEGQ=%d -dLastPage=1 -sOutputFile=%s %s 2>/dev/null',
            $quality,
            escapeshellarg($outputImage),
            escapeshellarg($context['output'])
        );

        shell_exec($command);

        return ['status' => is_file($outputImage) ? 'generated' : 'failed', 'path' => $outputImage];
    }
}
