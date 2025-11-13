<?php

namespace PdfFit\Plugins;

final class CompressExtreme
{
    public function __invoke(array $context): array
    {
        $sizeAfter = ($context['sizeAfter'] ?? 0) / 1048576;
        if ($sizeAfter <= 5) {
            return ['status' => 'skipped', 'reason' => 'Output already lightweight.'];
        }

        return [
            'status' => 'suggested',
            'command' => sprintf('pdf_fit extreme %s', escapeshellarg($context['output'])),
        ];
    }
}
