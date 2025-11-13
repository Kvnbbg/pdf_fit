<?php

use PdfFit\Core\PdfLoader;
use PdfFit\Core\PdfProcessor;
use PdfFit\Core\Utils;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../autoload.php';

final class ProcessorTest extends TestCase
{
    public function testProcessorFallsBackWithoutGhostscript(): void
    {
        if (Utils::commandExists('gs')) {
            $this->markTestSkipped('Ghostscript available, fallback scenario not applicable.');
        }

        $pdf = PdfLoader::load(__DIR__ . '/Fixtures/example.pdf');
        $result = PdfProcessor::process($pdf, ['type' => 'compress', 'quality' => 60, 'dpi' => 150]);
        $this->assertSame($pdf['binary'], $result['binary']);
    }
}
