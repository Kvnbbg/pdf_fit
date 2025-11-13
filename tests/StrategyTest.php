<?php

use PdfFit\Core\StrategySelector;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../autoload.php';

final class StrategyTest extends TestCase
{
    public function testSmartSelectsExtremeForHugeFiles(): void
    {
        $analysis = ['isHuge' => true, 'isLarge' => false, 'isMedium' => false];
        $strategy = StrategySelector::select('smart', $analysis);
        $this->assertSame('compress', $strategy['type']);
        $this->assertLessThanOrEqual(30, $strategy['quality']);
    }

    public function testResizeDefaults(): void
    {
        $strategy = StrategySelector::select('resize', [], []);
        $this->assertSame(1080, $strategy['width']);
        $this->assertSame(1920, $strategy['height']);
    }
}
