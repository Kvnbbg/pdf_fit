<?php

namespace PdfFit\Plugins;

use PdfFit\Core\Utils;

final class MetadataExtractor
{
    public function __invoke(array $context): array
    {
        $metadata = [
            'size_mb'   => $context['analysis']['sizeMB'] ?? null,
            'pageCount' => $context['analysis']['pageCount'] ?? null,
        ];

        if (Utils::commandExists('pdfinfo')) {
            $info = shell_exec(sprintf('pdfinfo %s 2>/dev/null', escapeshellarg($context['output'])));
            if (is_string($info)) {
                foreach (['Title', 'Author', 'Creator'] as $field) {
                    if (preg_match(sprintf('/%s:\s+(.+)/', $field), $info, $match)) {
                        $metadata[strtolower($field)] = trim($match[1]);
                    }
                }
            }
        }

        return $metadata;
    }
}
