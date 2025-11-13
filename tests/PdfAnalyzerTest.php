<?php

use PdfFit\Core\PdfAnalyzer;
use PdfFit\Core\PdfLoader;
use PdfFit\Core\Utils;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../autoload.php';

final class PdfAnalyzerTest extends TestCase
{
    public function testDetectsTinyPdf(): void
    {
        $pdf = PdfLoader::load(__DIR__ . '/Fixtures/example.pdf');
        $analysis = PdfAnalyzer::analyze($pdf);

        $this->assertTrue($analysis['isTiny']);
        if (Utils::commandExists('pdfinfo')) {
            $this->assertSame(1, $analysis['pageCount']);
        }
    }
}
