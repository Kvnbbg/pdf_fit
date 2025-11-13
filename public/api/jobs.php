<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/src/AgentFreelance.php';

try {
    $configPath = dirname(__DIR__, 2) . '/job.json';
    $agent = new AgentFreelance($configPath);
    $payload = [
        'status' => 'ok',
        'data' => $agent->export(),
    ];

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(500);
    $error = [
        'status' => 'error',
        'message' => $exception->getMessage(),
    ];

    echo json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
