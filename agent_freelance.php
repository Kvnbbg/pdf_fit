<?php

declare(strict_types=1);

/**
 * Agent Freelance CLI (PHP)
 * --------------------------------------------------------------
 * • Reads a job.json configuration file
 * • Merges the freelancer profile with default branding information
 * • Analyses one or many missions and generates tailored pitches
 * • Prints reusable assets (platform list, routines, comparative table)
 */

const CONFIG_FILE = __DIR__ . '/job.json';

// -----------------------------------------------------------------------------
// Data loading helpers
// -----------------------------------------------------------------------------

function load_config(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("Configuration file not found: {$path}");
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException("Unable to read configuration file: {$path}");
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        throw new RuntimeException("Invalid JSON content in configuration file: {$path}");
    }

    return $decoded;
}

function default_freelancer_profile(): array
{
    return [
        'name' => 'Kevin MARVILLE',
        'brand' => 'Tech & Stream',
        'site_main' => 'https://kvnbbg.fr',
        'site_brand' => 'https://techandstream.com',
        'tagline_fr' => 'Développeur full-stack & créateur Tech, FR/EN, orienté résultats.',
        'tagline_en' => 'Full-stack developer & indie tech creator (FR/EN), result-driven.',
        'years_experience' => 7,
        'top_platforms' => ['Malt', 'Upwork', 'Fiverr', 'FreelanceRepublik', 'Codeur'],
        'social_links' => [
            'linkedin' => 'https://linkedin.com/in/kevin-marville',
            'instagram' => 'https://instagram.com/techandstream',
            'youtube' => 'https://www.youtube.com/@techandstream',
            'twitch' => 'https://www.twitch.tv/techandstream',
        ],
        'content_pillars' => [
            'live coding & indie hacking streams',
            'automation, AI assistants & growth tooling',
            'technical SEO & conversion-driven landing pages',
        ],
    ];
}

function merge_freelancer_profile(array $custom): array
{
    $base = default_freelancer_profile();

    foreach ($custom as $key => $value) {
        if (is_array($value) && array_key_exists($key, $base) && is_array($base[$key])) {
            $base[$key] = array_replace_recursive($base[$key], $value);
            continue;
        }

        $base[$key] = $value;
    }

    return $base;
}

// -----------------------------------------------------------------------------
// Platform metadata & utilities
// -----------------------------------------------------------------------------

function get_platform_metadata(): array
{
    return [
        'codeur' => [
            'label' => 'Codeur.com',
            'type' => 'generalist',
            'notes' => 'Volume important, idéal pour obtenir des premiers avis rapidement.'
        ],
        'malt' => [
            'label' => 'Malt.fr',
            'type' => 'generalist',
            'notes' => 'Clients PME/ETI/startups, TJM élevés, missions longues.'
        ],
        'upwork' => [
            'label' => 'Upwork.com',
            'type' => 'generalist',
            'notes' => 'Marché international, idéal pour missions web/automation/AI.'
        ],
        'freelancer' => [
            'label' => 'Freelancer.com',
            'type' => 'generalist',
            'notes' => 'Très grand volume mais compétition forte et budgets variables.'
        ],
        'peopleperhour' => [
            'label' => 'PeoplePerHour.com',
            'type' => 'generalist',
            'notes' => 'Forte présence UK, bon mix missions rapides et récurrentes.'
        ],
        'fiverr' => [
            'label' => 'Fiverr.com',
            'type' => 'gig',
            'notes' => 'Parfait pour offres packagées et revenus semi-passifs.'
        ],
        'comeup' => [
            'label' => 'ComeUp.com',
            'type' => 'gig',
            'notes' => 'Version FR des gigs, efficace pour micro-prestations et visibilité.'
        ],
        'freelance' => [
            'label' => 'Freelance.com',
            'type' => 'generalist',
            'notes' => 'Axée entreprises et grands comptes, contrats plus longs.'
        ],
        'graphiste' => [
            'label' => 'Graphiste.com',
            'type' => 'design',
            'notes' => 'Communauté créative, bonne passerelle pour offres UI/branding.'
        ],
        'redacteur' => [
            'label' => 'Redacteur.com',
            'type' => 'writing',
            'notes' => 'Idéal pour SEO content et optimisation éditoriale.'
        ],
        'traduc' => [
            'label' => 'Traduc.com',
            'type' => 'translation',
            'notes' => 'Marché ciblé pour prestations de traduction rapide.'
        ],
        'toptal' => [
            'label' => 'Toptal.com',
            'type' => 'premium',
            'notes' => 'Sélection élite, expose à des clients internationaux premium.'
        ],
        'freelancerepublik' => [
            'label' => 'FreelanceRepublik.com',
            'type' => 'premium',
            'notes' => 'Focus dev senior remote, processus de sélection crédibilisant.'
        ],
        'guru' => [
            'label' => 'Guru.com',
            'type' => 'generalist',
            'notes' => 'Orientation business & développement, niche complémentaire.'
        ],
        'workingnotworking' => [
            'label' => 'WorkingNotWorking.com',
            'type' => 'creative',
            'notes' => 'Réseau créatif premium, idéal pour branding vidéo & motion.'
        ],
        'dribbble' => [
            'label' => 'Dribbble Freelance',
            'type' => 'design',
            'notes' => 'Cible UI/UX internationale, parfaite pour études de cas design.'
        ],
        'behance' => [
            'label' => 'Behance Jobs',
            'type' => 'design',
            'notes' => 'Visibilité Adobe, opportunités créatives globales.'
        ],
        'weworkremotely' => [
            'label' => 'WeWorkRemotely',
            'type' => 'remote_jobs',
            'notes' => 'Offres full remote, très tech oriented.'
        ],
        'angellist' => [
            'label' => 'AngelList Talent',
            'type' => 'startups',
            'notes' => 'Startups en croissance, possibilité d’equity ou missions hybrides.'
        ],
    ];
}

function normalize_platform(string $platform): string
{
    $normalized = strtolower($platform);
    $normalized = str_replace(['.com', '.fr'], '', $normalized);
    return preg_replace('/[^a-z]/', '', $normalized) ?? $normalized;
}

function is_top_platform(string $platformKey, array $topPlatforms): bool
{
    return in_array(ucfirst($platformKey), array_map('ucfirst', $topPlatforms), true);
}

// -----------------------------------------------------------------------------
// Job analysis helpers
// -----------------------------------------------------------------------------

function analyze_job(array $job): array
{
    $text = strtolower($job['text'] ?? '');

    $goal = match (true) {
        str_contains($text, 'site vitrine') => 'Créer ou refondre un site vitrine orienté conversion',
        str_contains($text, 'e-commerce') || str_contains($text, 'woocommerce') || str_contains($text, 'boutique') => 'Mettre en place ou optimiser une boutique en ligne',
        str_contains($text, 'seo') => 'Améliorer l’acquisition via SEO & contenu technique',
        str_contains($text, 'automation') || str_contains($text, 'automatisation') => 'Construire ou optimiser un workflow automatisé',
        str_contains($text, 'api') => 'Concevoir ou intégrer une API fiable et documentée',
        str_contains($text, 'application') || str_contains($text, 'web app') => 'Développer ou scaler une application web',
        default => 'Concevoir une solution digitale adaptée au besoin annoncé',
    };

    $keywords = [
        'wordpress', 'shopify', 'laravel', 'symfony', 'react', 'vue', 'nuxt', 'next',
        'php', 'python', 'node', 'javascript', 'typescript', 'tailwind', 'api', 'rest',
        'graphql', 'ai', 'notion', 'zapier', 'bubble'
    ];

    $technologies = [];
    foreach ($keywords as $keyword) {
        if (str_contains($text, $keyword)) {
            $technologies[] = $keyword;
        }
    }
    $technologies = array_values(array_unique($technologies));

    $budget = null;
    if (preg_match('/(\d+[\s\u00A0]?\d*)\s*(€|eur|euros)/i', $job['text'] ?? '', $matches)) {
        $budget = (int) str_replace(' ', '', $matches[1]);
    }

    $deadline = 'Non précisée';
    if (str_contains($text, 'urgent')) {
        $deadline = 'Urgent';
    } elseif (str_contains($text, 'semaine')) {
        $deadline = 'Cette semaine';
    } elseif (str_contains($text, 'mois')) {
        $deadline = 'Dans le mois';
    }

    $redFlags = [];
    if ($budget !== null && $budget < 300) {
        $redFlags[] = 'Budget très bas pour une prestation professionnelle.';
    }
    if (str_contains($text, 'petit budget') || str_contains($text, 'budget serré')) {
        $redFlags[] = 'Budget annoncé comme limité.';
    }
    if (strlen($job['text'] ?? '') < 180) {
        $redFlags[] = 'Annonce courte, nécessite clarification du périmètre.';
    }

    return [
        'goal' => $goal,
        'technologies' => $technologies,
        'budget' => $budget,
        'deadline' => $deadline,
        'red_flags' => $redFlags,
    ];
}

function evaluate_job(array $analysis, array $constraints, array $freelancer): array
{
    $budget = $analysis['budget'];
    $minBudget = $constraints['min_budget'] ?? 0;
    $score = 0;

    if ($budget !== null) {
        if ($budget >= $minBudget) {
            $score += 2;
        } elseif ($budget >= $minBudget * 0.6) {
            $score += 1;
        } else {
            $score -= 2;
        }
    }

    $score -= count($analysis['red_flags']);

    $grade = 'B';
    $explanation = "Mission intéressante mais à surveiller : certains points méritent clarification.";

    if ($score >= 2) {
        $grade = 'A';
        $explanation = sprintf(
            "Mission très intéressante pour %s : budget aligné et signaux au vert.",
            $freelancer['brand']
        );
    } elseif ($score < 0) {
        $grade = 'C';
        $explanation = "Mission à éviter dans l’état actuel : budget faible ou signaux d’alerte.";
    }

    return [
        'grade' => $grade,
        'score' => $score,
        'explanation' => $explanation,
    ];
}

// -----------------------------------------------------------------------------
// Pitch generation
// -----------------------------------------------------------------------------

function generate_pitch(array $job, array $analysis, array $evaluation, array $freelancer): string
{
    $platformKey = normalize_platform($job['platform'] ?? '');
    $platformMeta = get_platform_metadata()[$platformKey] ?? null;
    $language = strtolower($job['language'] ?? 'fr');
    $goal = $analysis['goal'];
    $techList = !empty($analysis['technologies'])
        ? implode(', ', $analysis['technologies'])
        : 'une stack web moderne (PHP, JS, APIs, automatisation)';
    $grade = $evaluation['grade'];
    $isTopPlatform = is_top_platform($platformKey, $freelancer['top_platforms'] ?? []);

    if ($language === 'fr') {
        return generate_pitch_fr($platformMeta, $goal, $techList, $grade, $freelancer, $isTopPlatform);
    }

    return generate_pitch_en($platformMeta, $goal, $techList, $grade, $freelancer, $isTopPlatform);
}

function generate_pitch_fr(?array $platformMeta, string $goal, string $techList, string $grade, array $freelancer, bool $isTopPlatform): string
{
    $name = $freelancer['name'];
    $brand = $freelancer['brand'];
    $tagline = $freelancer['tagline_fr'];
    $site = $freelancer['site_brand'];

    $opening = $grade === 'A'
        ? "Bonjour,\n\nVotre mission correspond parfaitement à ma manière de travailler."
        : "Bonjour,\n\nVotre mission m’intéresse et je peux vous proposer une approche structurée.";

    $platformLine = '';
    if ($platformMeta !== null) {
        $platformLine = sprintf(
            "Je collabore régulièrement via %s, ce qui sécurise échanges et suivi.\n\n",
            $platformMeta['label']
        );
    }
    if ($isTopPlatform) {
        $platformLine .= "C’est l’une de mes plateformes principales pour des collaborations sérieuses.\n\n";
    }

    return sprintf(
        "%s\nJe suis %s, développeur full-stack derrière **%s** (%s).\n%s\nObjectif identifié : **%s**.\nStack maîtrisée : %s.\n\n%sCe que je propose :\n• Cadrage rapide (objectifs, contraintes, livrables)\n• Implémentation propre, documentée, orientée performance\n• Communication proactive + reporting clair\n\nPlan en 3 étapes :\n1. Échange rapide pour valider le périmètre et le budget\n2. Plan d’action chiffré avec jalons\n3. Réalisation + ajustements jusqu’au go-live\n\nQuestions pour avancer :\n1. Disposez-vous d’un cahier des charges ou d’exemples de référence ?\n2. Y a-t-il des intégrations ou contraintes techniques à anticiper ?\n3. Quelle est la date idéale de mise en production ?\n\nSouhaitez-vous que je vous prépare un plan d’action synthétique ?",
        $opening,
        $name,
        $brand,
        $site,
        $tagline,
        $goal,
        $techList,
        $platformLine
    );
}

function generate_pitch_en(?array $platformMeta, string $goal, string $techList, string $grade, array $freelancer, bool $isTopPlatform): string
{
    $name = $freelancer['name'];
    $brand = $freelancer['brand'];
    $tagline = $freelancer['tagline_en'];
    $site = $freelancer['site_brand'];

    $opening = $grade === 'A'
        ? "Hi,\n\nYour project is an excellent fit for how I work."
        : "Hi,\n\nI’d love to help and can bring a clear, structured execution plan.";

    $platformLine = '';
    if ($platformMeta !== null) {
        $platformLine = sprintf(
            "I often collaborate through %s, which keeps communication, billing and follow-up smooth.\n\n",
            $platformMeta['label']
        );
    }
    if ($isTopPlatform) {
        $platformLine .= "It’s also one of my main platforms to work with long-term, high-trust clients.\n\n";
    }

    return sprintf(
        "%s\nI’m %s, full-stack developer running **%s** (%s).\n%s\nMain goal identified: **%s**.\nUsual stack: %s.\n\n%sHere’s my 3-step plan:\n1. Quick sync (chat or call) to confirm scope, timeline and budget\n2. Technical action plan with milestones + deliverables\n3. Implementation with proactive updates until we hit your KPIs\n\nWhy clients keep working with me:\n• Clean architecture, security-first mindset\n• Fast feedback loops, async-friendly communication\n• FR/EN bilingual and used to distributed teams\n\nQuestions to align:\n1. Do you already have an existing product or assets to plug into ?\n2. Any integrations or stakeholders I should account for ?\n3. What’s the target launch date or key milestone ?\n\nWould you like me to send a concise technical proposal to get started ?",
        $opening,
        $name,
        $brand,
        $site,
        $tagline,
        $goal,
        $techList,
        $platformLine
    );
}

// -----------------------------------------------------------------------------
// Rendering helpers
// -----------------------------------------------------------------------------

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

function render_platform_catalog(array $freelancer): void
{
    echo "=== PLATEFORMES FREELANCE ===\n";

    $metadata = get_platform_metadata();
    $categories = [];
    foreach ($metadata as $key => $data) {
        $categories[$data['type']][$key] = $data;
    }

    foreach ($categories as $type => $platforms) {
        printf("%s :\n", strtoupper($type));
        foreach ($platforms as $platform) {
            printf("  - %s — %s\n", $platform['label'], $platform['notes']);
        }
        echo PHP_EOL;
    }

    if (!empty($freelancer['top_platforms'])) {
        echo "Top 5 focus :\n";
        foreach ($freelancer['top_platforms'] as $index => $platformName) {
            printf("  %d. %s\n", $index + 1, $platformName);
        }
        echo PHP_EOL;
    }
}

function render_routines(): void
{
    echo "=== ROUTINE CLIENTS ===\n";
    echo "Routine 20 min / jour :\n";
    echo "  1. 5 min — Vérifier Codeur, Malt, Upwork, LinkedIn inbox\n";
    echo "  2. 10 min — Répondre à 3-5 missions qualifiées (ouverture personnalisée)\n";
    echo "  3. 5 min — Publier un micro-post (LinkedIn, IG story, devlog)\n\n";

    echo "Routine boost 48h :\n";
    echo "  Jour 1 : 20 candidatures premium (Malt/Upwork) + création gig Fiverr + 3 commentaires LinkedIn + 1 post technique\n";
    echo "  Jour 2 : 10 candidatures Codeur + ajout portfolio + 5 DM ciblés + optimisation gig Fiverr\n\n";
}

function render_comparative_table(): void
{
    echo "=== TABLEAU COMPARATIF ===\n";
    $rows = [
        ['Malt', '★★★', '★★★★★', '★★★', '★★', 'Dev, consulting', 'Clients FR/UE premium'],
        ['Upwork', '★★★', '★★★★★', '★★★', '★★★★', 'Dev, AI, automation', 'Très rentable si profil optimisé'],
        ['Fiverr', '★★', '★★★★', '★★★★★', '★★★', 'Offres packagées', 'Bon pour revenu récurrent'],
        ['Codeur', '★★', '★★★', '★★★', '★★★★★', 'Quick wins, premiers avis', 'Volume élevé, besoin pitch solide'],
        ['FreelanceRepublik', '★★★★', '★★★★★', '★★★', '★★', 'Dev senior remote', 'Sélection stricte, clients qualifiés'],
        ['ComeUp', '★★', '★★', '★★★★', '★★★', 'Micro services', 'Idéal pour démarrer et visibilité'],
        ['Freelancer.com', '★★★', '★★★', '★★', '★★★★★', 'Mix projets', 'Budgets variables, filtrage nécessaire'],
        ['Graphiste.com', '★', '★★', '★★★', '★★', 'Design', 'Parfait pour offres créatives'],
        ['Redacteur.com', '★', '★★', '★★★', '★★★', 'Rédaction SEO', 'Complément contenu'],
        ['Traduc.com', '★', '★★', '★★★', '★★', 'Traduction', 'Marché spécialisé'],
    ];

    printf("%-22s | %-9s | %-15s | %-8s | %-11s | %-22s | %s\n", 'Plateforme', 'Difficulté', 'Gains potentiels', 'Rapidité', 'Compétition', 'Idéal pour', 'Notes');
    echo str_repeat('-', 120) . "\n";

    foreach ($rows as $row) {
        printf(
            "%-22s | %-9s | %-15s | %-8s | %-11s | %-22s | %s\n",
            ...$row
        );
    }

    echo PHP_EOL;
}

// -----------------------------------------------------------------------------
// Job rendering pipeline
// -----------------------------------------------------------------------------

function render_job(array $job, array $freelancer, int $index): void
{
    printf("=== MISSION #%d — %s ===\n", $index + 1, $job['platform']);
    $analysis = analyze_job($job);
    $evaluation = evaluate_job($analysis, $job['constraints'] ?? [], $freelancer);
    $pitch = generate_pitch($job, $analysis, $evaluation, $freelancer);

    echo "Analyse rapide :\n";
    printf("  • Objectif : %s\n", $analysis['goal']);
    printf("  • Techno : %s\n", $analysis['technologies'] ? implode(', ', $analysis['technologies']) : 'à préciser');
    printf("  • Deadline : %s\n", $analysis['deadline']);
    printf("  • Budget : %s\n", $analysis['budget'] !== null ? $analysis['budget'] . ' €' : 'Non communiqué');
    if (!empty($analysis['red_flags'])) {
        printf("  • Risques : %s\n", implode(' / ', $analysis['red_flags']));
    }

    echo PHP_EOL;
    echo "Stratégie :\n";
    printf("  Grade : %s (score %d)\n", $evaluation['grade'], $evaluation['score']);
    printf("  Commentaire : %s\n\n", $evaluation['explanation']);

    echo "Pitch :\n";
    echo $pitch . PHP_EOL . PHP_EOL;
}

// -----------------------------------------------------------------------------
// Main program
// -----------------------------------------------------------------------------

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0] ?? '')) {
    try {
        $config = load_config(CONFIG_FILE);
        $freelancer = merge_freelancer_profile($config['freelancer'] ?? []);

        $jobs = [];
        if (isset($config['jobs']) && is_array($config['jobs'])) {
            $jobs = $config['jobs'];
        } elseif (isset($config['job'])) {
            $jobs = [$config['job']];
        }

        if ($jobs === []) {
            throw new RuntimeException('No jobs provided in job.json.');
        }

        render_profile($freelancer);
        render_platform_catalog($freelancer);
        render_routines();
        render_comparative_table();

        foreach ($jobs as $index => $job) {
            render_job($job, $freelancer, $index);
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, 'Erreur : ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
