<?php

declare(strict_types=1);

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
        window.AGENT_DATA = <?= json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG
        ) ?>;
    </script>
    <script src="assets/app.js" defer></script>
</body>
</html>
