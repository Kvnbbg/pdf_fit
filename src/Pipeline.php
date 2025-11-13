<?php

namespace PdfFit;

use PdfFit\Support\Stopwatch;

class Pipeline
{
    private string $mode;
    private string $file;
    private array $options;

    public function __construct(string $mode, string $file, array $options = [])
    {
        $this->mode = $mode;
        $this->file = $file;
        $this->options = $options;
    }

    public function run(): array
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start();

        Logger::info('🔍 Loading PDF...');
        $pdf = PdfLoader::load($this->file);

        Logger::info('📊 Analyzing PDF...');
        $analysis = PdfAnalyzer::analyze($pdf);

        Logger::info('🎯 Selecting strategy...');
        $strategy = StrategySelector::select($this->mode, $analysis, $this->options);

        Logger::info('⚙️  Processing PDF...');
        $processed = PdfProcessor::process($pdf, $strategy);

        Logger::info('📦 Exporting result...');
        $outputFile = PdfExporter::export($processed, $this->file, $strategy);

        $elapsed = $stopwatch->stop();

        return [
            'input'      => $this->file,
            'output'     => $outputFile,
            'analysis'   => $analysis,
            'strategy'   => $strategy,
            'sizeBefore' => $pdf['size'],
            'sizeAfter'  => filesize($outputFile) ?: $pdf['size'],
            'duration'   => $elapsed,
        ];
    }
}
