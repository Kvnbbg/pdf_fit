<?php

namespace PdfFit\Core;

final class Stopwatch
{
    private float $start = 0.0;

    public function start(): void
    {
        $this->start = microtime(true);
    }

    public function stop(): float
    {
        if ($this->start === 0.0) {
            return 0.0;
        }

        return microtime(true) - $this->start;
    }
}
