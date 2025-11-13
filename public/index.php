<?php

require __DIR__ . '/../autoload.php';

use PdfFit\Core\Pipeline;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    return;
}

if (!isset($_FILES['pdf']) || !is_uploaded_file($_FILES['pdf']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing pdf file']);
    return;
}

$mode = $_POST['mode'] ?? 'smart';
$options = $_POST;
unset($options['mode']);

$pipeline = new Pipeline($mode, $_FILES['pdf']['tmp_name'], $options);
$result = $pipeline->run();

$payload = [
    'input'    => $result['input'],
    'output'   => $result['output'],
    'analysis' => $result['analysis'],
    'strategy' => $result['strategy'],
    'size'     => [
        'before' => $result['sizeBefore'],
        'after'  => $result['sizeAfter'],
    ],
    'duration' => $result['duration'],
    'plugins'  => $result['plugins'],
];

echo json_encode($payload, JSON_PRETTY_PRINT);
