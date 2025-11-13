<?php

spl_autoload_register(function (string $class): void {
    $prefix = 'PdfFit\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $relativePath = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($relativePath)) {
            require $relativePath;
        }
    }
});
