<?php

namespace PdfFit\Cli;

use InvalidArgumentException;

final class ArgvParser
{
    public function parse(array $argv): array
    {
        $args = $argv;
        array_shift($args); // remove script name
        if (empty($args)) {
            throw new InvalidArgumentException($this->usage());
        }

        $command = strtolower(array_shift($args));
        $options = $this->extractOptions($args);
        $target = $options['__target'] ?? null;

        return [$command, $target, $options['params']];
    }

    private function extractOptions(array $args): array
    {
        $params = [];
        $target = null;

        while (!empty($args)) {
            $arg = array_shift($args);
            if (str_starts_with($arg, '--')) {
                $pair = explode('=', substr($arg, 2), 2);
                $key = $pair[0];
                $value = $pair[1] ?? (array_shift($args) ?: 'true');
                $params[$key] = $value;
            } elseif ($target === null) {
                $target = $arg;
            } else {
                $params[] = $arg;
            }
        }

        return ['__target' => $target, 'params' => $params];
    }

    private function usage(): string
    {
        return <<<TXT
Usage: pdf_fit <command> <target> [options]

Commands:
  smart|compress|optimize|extreme <file.pdf>
  resize <file.pdf> --width=1080 --height=1920
  batch <directory> [--mode=smart]
  server [--host=0.0.0.0 --port=8080]
TXT;
    }
}
