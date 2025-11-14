<?php

namespace PdfFit\Api;

use PdfFit\Core\Logger;

final class Server
{
    public static function run(string $host = '0.0.0.0', int $port = 8080, ?string $documentRoot = null): void
    {
        $docRoot = $documentRoot !== null ? realpath($documentRoot) : realpath(__DIR__ . '/../../public');
        if ($docRoot === false) {
            throw new \RuntimeException('Unable to locate public directory.');
        }

        $address = sprintf('%s:%d', $host, $port);
        $command = sprintf(
            'php -S %s -t %s',
            escapeshellarg($address),
            escapeshellarg($docRoot)
        );
        Logger::headline('PDF Fit API');
        Logger::info("Serving on http://{$host}:{$port} (Ctrl+C to stop)");
        passthru($command);
    }
}
