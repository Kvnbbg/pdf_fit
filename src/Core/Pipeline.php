<?php

namespace PdfFit\Core;

use PdfFit\Plugins\PluginManager;

final class Pipeline
{
    public function __construct(
        private readonly string $mode,
        private readonly string $file,
        private readonly array $options = []
    ) {
    }

    public function run(): array
    {
        PluginManager::bootDefaults();

        $stopwatch = new Stopwatch();
        $stopwatch->start();

        Logger::info('🔍 Loading PDF');
        $pdf = PdfLoader::load($this->file);

        Logger::info('📊 Analyzing content');
        $analysis = PdfAnalyzer::analyze($pdf);

        Logger::info('🎯 Selecting strategy');
        $strategy = StrategySelector::select($this->mode, $analysis, $this->options);

        Logger::info('⚙️  Processing via Ghostscript');
        $processed = PdfProcessor::process($pdf, $strategy);

        Logger::info('📦 Exporting artifact');
        $output = PdfExporter::export($processed, $this->file, $strategy);

        $duration = $stopwatch->stop();

        $sizeBefore = $pdf['size'];
        $sizeAfter = filesize($output) ?: $sizeBefore;

        $context = [
            'input'       => $pdf['path'],
            'output'      => $output,
            'analysis'    => $analysis,
            'strategy'    => $strategy,
            'sizeBefore'  => $sizeBefore,
            'sizeAfter'   => $sizeAfter,
        ];

        Logger::info('🔌 Running plugins');
        $plugins = PluginManager::run($context);

        Logger::success('Pipeline completed');

        return [
            'input'      => $pdf['path'],
            'output'     => $output,
            'analysis'   => $analysis,
            'strategy'   => $strategy,
            'sizeBefore' => $sizeBefore,
            'sizeAfter'  => $sizeAfter,
            'duration'   => $duration,
            'plugins'    => $plugins,
        ];
    }
}
