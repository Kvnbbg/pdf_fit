<?php

namespace PdfFit\Support;

class Stopwatch
{
    private float $startedAt = 0.0;

    public function start(): void
    {
        $this->startedAt = microtime(true);
    }

    public function stop(): float
    {
        if ($this->startedAt === 0.0) {
            return 0.0;
        }

        return round(microtime(true) - $this->startedAt, 4);
    }
}
