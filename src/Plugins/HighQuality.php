<?php

namespace PdfFit\Plugins;

final class HighQuality
{
    public function __invoke(array $context): array
    {
        $analysis = $context['analysis'];
        if (($analysis['imageDensity'] ?? 0) > 2) {
            return [
                'status' => 'notice',
                'message' => 'High image density detected. Consider optimize mode for sharper exports.',
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'Quality balanced for mixed content.',
        ];
    }
}
