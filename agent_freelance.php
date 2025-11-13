<?php

declare(strict_types=1);

require_once __DIR__ . '/src/AgentFreelance.php';

const CONFIG_FILE = __DIR__ . '/job.json';

function render_profile(array $freelancer): void
{
    echo "=== PROFIL FREELANCE ===\n";
    printf("Nom : %s\n", $freelancer['name']);
    printf("Brand : %s\n", $freelancer['brand']);
    printf("Sites : %s | %s\n", $freelancer['site_main'], $freelancer['site_brand']);
    printf("Tagline FR : %s\n", $freelancer['tagline_fr']);
    printf("Tagline EN : %s\n", $freelancer['tagline_en']);
    printf("Expérience : %d ans\n", $freelancer['years_experience']);

    if (!empty($freelancer['social_links'])) {
        echo "Réseaux :\n";
        foreach ($freelancer['social_links'] as $network => $url) {
            printf("  - %s : %s\n", ucfirst($network), $url);
        }
    }

    if (!empty($freelancer['content_pillars'])) {
        echo "Piliers de contenu :\n";
        foreach ($freelancer['content_pillars'] as $pillar) {
            printf("  • %s\n", ucfirst($pillar));
        }
    }

    echo PHP_EOL;
}

function render_platform_catalog(array $catalog, array $topPlatforms): void
{
    echo "=== PLATEFORMES FREELANCE ===\n";

    foreach ($catalog as $type => $platforms) {
        printf("%s :\n", strtoupper($type));
        foreach ($platforms as $platform) {
            printf("  - %s — %s\n", $platform['label'], $platform['notes']);
        }
        echo PHP_EOL;
    }

    if (!empty($topPlatforms)) {
        echo "Top 5 focus :\n";
        foreach ($topPlatforms as $index => $platformName) {
            printf("  %d. %s\n", $index + 1, $platformName);
        }
        echo PHP_EOL;
    }
}

function render_routines(array $routines): void
{
    echo "=== ROUTINE CLIENTS ===\n";
    echo $routines['daily']['title'] . ":\n";
    foreach ($routines['daily']['steps'] as $step) {
        printf("  - %s\n", $step);
    }
    echo PHP_EOL;

    echo $routines['boost']['title'] . ":\n";
    foreach ($routines['boost']['days'] as $day) {
        printf("  %s :\n", $day['label']);
        foreach ($day['actions'] as $action) {
            printf("    • %s\n", $action);
        }
    }
    echo PHP_EOL;
}

function render_comparative_table(array $rows): void
{
    echo "=== TABLEAU COMPARATIF ===\n";
    printf(
        "%-22s | %-9s | %-15s | %-8s | %-11s | %-22s | %s\n",
        'Plateforme',
        'Difficulté',
        'Gains potentiels',
        'Rapidité',
        'Compétition',
        'Idéal pour',
        'Notes'
    );
    echo str_repeat('-', 120) . "\n";

    foreach ($rows as $row) {
        printf(
            "%-22s | %-9s | %-15s | %-8s | %-11s | %-22s | %s\n",
            $row['platform'],
            $row['difficulty'],
            $row['earnings'],
            $row['speed'],
            $row['competition'],
            $row['ideal_for'],
            $row['notes']
        );
    }

    echo PHP_EOL;
}

function render_job(array $report, int $index): void
{
    $job = $report['job'];
    $analysis = $report['analysis'];
    $strategy = $report['strategy'];

    printf("=== MISSION #%d — %s ===\n", $index + 1, $job['platform']);

    echo "Analyse rapide :\n";
    printf("  • Objectif : %s\n", $analysis['goal']);
    printf(
        "  • Techno : %s\n",
        $analysis['technologies'] ? implode(', ', $analysis['technologies']) : 'à préciser'
    );
    printf("  • Deadline : %s\n", $analysis['deadline']);
    printf(
        "  • Budget : %s\n",
        $analysis['budget'] !== null ? $analysis['budget'] . ' €' : 'Non communiqué'
    );
    if (!empty($analysis['red_flags'])) {
        printf("  • Risques : %s\n", implode(' / ', $analysis['red_flags']));
    }

    echo PHP_EOL;
    echo "Stratégie :\n";
    printf("  Grade : %s (score %d)\n", $strategy['grade'], $strategy['score']);
    printf("  Commentaire : %s\n\n", $strategy['explanation']);

    echo "Pitch :\n";
    echo $report['pitch'] . PHP_EOL . PHP_EOL;
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0] ?? '')) {
    try {
        $agent = new AgentFreelance(CONFIG_FILE);

        $freelancer = $agent->getFreelancer();
        $platformCatalog = $agent->getPlatformCatalog();
        $routines = $agent->getRoutines();
        $comparativeTable = $agent->getComparativeTable();
        $reports = $agent->buildAllReports();

        render_profile($freelancer);
        render_platform_catalog($platformCatalog, $freelancer['top_platforms'] ?? []);
        render_routines($routines);
        render_comparative_table($comparativeTable);

        foreach ($reports as $index => $report) {
            render_job($report, $index);
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, 'Erreur : ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
