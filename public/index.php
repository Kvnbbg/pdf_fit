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

$outputFile = $result['output'];
$publishDir = __DIR__ . '/downloads';
if (!is_dir($publishDir) && !mkdir($publishDir, 0775, true) && !is_dir($publishDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to prepare download directory']);
    return;
}

$originalName = $_FILES['pdf']['name'] ?? 'document.pdf';
$baseName = pathinfo($originalName, PATHINFO_FILENAME) ?: 'document';
$baseName = preg_replace('/[^A-Za-z0-9_\-]+/', '-', $baseName);
if ($baseName === '') {
    $baseName = 'document';
}

try {
    $token = bin2hex(random_bytes(4));
} catch (\Throwable $exception) {
    $token = (string) time();
}

$filename = sprintf('%s-%s.pdf', $baseName, $token);
$publicPath = $publishDir . '/' . $filename;

if (!@rename($outputFile, $publicPath)) {
    if (!@copy($outputFile, $publicPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to publish optimized PDF']);
        return;
    }
    @unlink($outputFile);
}

@unlink($_FILES['pdf']['tmp_name']);

$downloadUrl = '/downloads/' . $filename;

$payload = [
    'input'    => $result['input'],
    'output'   => $publicPath,
    'analysis' => $result['analysis'],
    'strategy' => $result['strategy'],
    'size'     => [
        'before' => $result['sizeBefore'],
        'after'  => $result['sizeAfter'],
    ],
    'duration' => $result['duration'],
    'notes'    => $result['notes'],
    'plugins'  => $result['plugins'],
    'download' => $downloadUrl,
    'filename' => $filename,
];

echo json_encode($payload, JSON_PRETTY_PRINT);
return;

require_once dirname(__DIR__) . '/src/AgentFreelance.php';

$configPath = dirname(__DIR__) . '/job.json';
$agent = new AgentFreelance($configPath);
$data = $agent->export();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Freelance – Tech & Stream</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" integrity="sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtz1cCx0HOVG4rjvjx5w1u6k8r7B6U8p4GdC1ogqM7U4V1kP4hZHc1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <header class="hero">
        <div class="container">
            <h1>Agent Freelance – Tech & Stream</h1>
            <p>Analyse automatique des missions freelances et pitchs prêts à l’emploi.</p>
            <button id="refresh-btn" class="btn btn-primary">Actualiser depuis job.json</button>
            <p id="status-message" class="status" role="status" aria-live="polite"></p>
        </div>
    </header>

    <main class="container">
        <noscript>
            <div class="alert">Ce tableau de bord nécessite JavaScript pour afficher les informations dynamiques.</div>
        </noscript>

        <section aria-labelledby="profile-title" class="card" id="profile-section">
            <h2 id="profile-title">Profil freelance</h2>
            <div id="profile-card"></div>
        </section>

        <section aria-labelledby="platform-title" class="card" id="platform-section">
            <h2 id="platform-title">Plateformes & top focus</h2>
            <div id="platform-catalog"></div>
        </section>

        <section aria-labelledby="routine-title" class="card" id="routine-section">
            <h2 id="routine-title">Routines clients</h2>
            <div id="routines"></div>
        </section>

        <section aria-labelledby="table-title" class="card" id="table-section">
            <h2 id="table-title">Tableau comparatif</h2>
            <div class="table-wrapper">
                <table id="comparative-table">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="missions-title" class="card" id="missions-section">
            <h2 id="missions-title">Missions analysées</h2>
            <div id="missions"></div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>Branding Tech & Stream • <?= htmlspecialchars($data['profile']['site_brand'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> • <?= htmlspecialchars($data['generated_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        </div>
    </footer>

    <script>
        window.AGENT_DATA = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>;
    </script>
    <script src="assets/app.js" defer></script>
</body>
</html>
