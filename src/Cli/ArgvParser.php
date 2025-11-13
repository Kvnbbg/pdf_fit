<?php

namespace PdfFit\Cli;

use InvalidArgumentException;

class ArgvParser
{
    private const SUPPORTED_MODES = ['smart', 'compress', 'resize'];

    public function parse(array $argv): array
    {
        $args = $argv;
        array_shift($args); // remove script name

        if (empty($args)) {
            throw new InvalidArgumentException($this->usage("Missing arguments"));
        }

        $mode = strtolower(array_shift($args));
        if (!in_array($mode, self::SUPPORTED_MODES, true)) {
            throw new InvalidArgumentException($this->usage("Unsupported mode '{$mode}'"));
        }

        $file = array_shift($args);
        if ($file === null) {
            throw new InvalidArgumentException($this->usage("PDF file path is required"));
        }

        if (!is_file($file)) {
            throw new InvalidArgumentException("File not found: {$file}");
        }

        $options = $this->parseOptions($args);

        return [$mode, $file, $options];
    }

    private function parseOptions(array $args): array
    {
        $options = [];
        $pendingKey = null;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $trimmed = substr($arg, 2);
                if (str_contains($trimmed, '=')) {
                    [$key, $value] = explode('=', $trimmed, 2);
                    $options[$key] = $this->normalizeValue($value);
                } else {
                    $pendingKey = $trimmed;
                }
            } elseif ($pendingKey !== null) {
                $options[$pendingKey] = $this->normalizeValue($arg);
                $pendingKey = null;
            } else {
                $options[] = $arg;
            }
        }

        if ($pendingKey !== null) {
            $options[$pendingKey] = true;
        }

        return $options;
    }

    private function normalizeValue(string $value): mixed
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        $lower = strtolower($value);
        if (in_array($lower, ['true', 'false'], true)) {
            return $lower === 'true';
        }

        return $value;
    }

    private function usage(string $error): string
    {
        $modes = implode(', ', self::SUPPORTED_MODES);

        return <<<TXT
{$error}
Usage: pdf_fit <mode> <file.pdf> [options]

Modes: {$modes}
Examples:
  pdf_fit smart invoice.pdf
  pdf_fit compress report.pdf --quality=60
  pdf_fit resize slides.pdf --width=1080 --height=1920
TXT;
    }
}
