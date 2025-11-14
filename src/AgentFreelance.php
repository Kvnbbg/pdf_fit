<?php

declare(strict_types=1);

final class AgentFreelance
{
    private array $freelancer;
    private array $jobs;

    public function __construct(string $configPath)
    {
        $config = self::loadConfig($configPath);
        $this->freelancer = self::mergeFreelancerProfile($config['freelancer'] ?? []);
        $this->jobs = self::extractJobs($config);
    }

    public function getFreelancer(): array
    {
        return $this->freelancer;
    }

    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function getPlatformMetadata(): array
    {
        return self::platformMetadata();
    }

    public function getPlatformCatalog(): array
    {
        $catalog = [];
        foreach (self::platformMetadata() as $key => $meta) {
            $catalog[$meta['type']][] = $meta + ['key' => $key];
        }

        ksort($catalog);

        return $catalog;
    }

    public function getRoutines(): array
    {
        return self::routines();
    }

    public function getComparativeTable(): array
    {
        return self::comparativeTable();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildAllReports(): array
    {
        $reports = [];
        foreach ($this->jobs as $index => $job) {
            $reports[] = $this->buildJobReport($job, $index);
        }

        return $reports;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildJobReport(array $job, int $index = 0): array
    {
        $analysis = self::analyzeJob($job);
        $strategy = self::evaluateJob($analysis, $job['constraints'] ?? [], $this->freelancer);
        $pitch = self::generatePitch($job, $analysis, $strategy, $this->freelancer);
        $platformKey = self::normalizePlatform($job['platform'] ?? '');
        $platformMeta = self::platformMetadata()[$platformKey] ?? null;

        return [
            'index' => $index,
            'job' => $job,
            'analysis' => $analysis,
            'strategy' => $strategy,
            'pitch' => $pitch,
            'platform_meta' => $platformMeta,
            'language' => strtolower($job['language'] ?? 'fr'),
        ];
    }

    public function export(): array
    {
        return [
            'profile' => $this->getFreelancer(),
            'platformCatalog' => $this->getPlatformCatalog(),
            'routines' => $this->getRoutines(),
            'comparativeTable' => $this->getComparativeTable(),
            'missions' => $this->buildAllReports(),
            'generated_at' => date(DATE_ATOM),
        ];
    }

    private static function loadConfig(string $path): array
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

    private static function defaultFreelancerProfile(): array
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

    private static function mergeFreelancerProfile(array $custom): array
    {
        $base = self::defaultFreelancerProfile();

        foreach ($custom as $key => $value) {
            if (is_array($value) && array_key_exists($key, $base) && is_array($base[$key])) {
                $base[$key] = array_replace_recursive($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    private static function extractJobs(array $config): array
    {
        $jobs = [];
        if (isset($config['jobs']) && is_array($config['jobs'])) {
            $jobs = array_values(array_filter($config['jobs'], static fn ($job) => is_array($job)));
        } elseif (isset($config['job']) && is_array($config['job'])) {
            $jobs = [$config['job']];
        }

        if ($jobs === []) {
            throw new RuntimeException('No jobs provided in configuration.');
        }

        return $jobs;
    }

    private static function normalizePlatform(string $platform): string
    {
        $normalized = strtolower($platform);
        $normalized = str_replace(['.com', '.fr'], '', $normalized);

        return preg_replace('/[^a-z]/', '', $normalized) ?? $normalized;
    }

    private static function isTopPlatform(string $platformKey, array $topPlatforms): bool
    {
        return in_array(ucfirst($platformKey), array_map('ucfirst', $topPlatforms), true);
    }

    private static function analyzeJob(array $job): array
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
        if (preg_match('/(\d+[\s\u00A0]?\d*)\s*(€|eur|euros|\$|usd)/i', $job['text'] ?? '', $matches)) {
            $budget = (int) str_replace([' ', '\u00A0'], '', $matches[1]);
        }

        $deadline = 'Non précisée';
        if (str_contains($text, 'urgent')) {
            $deadline = 'Urgent';
        } elseif (str_contains($text, 'semaine')) {
            $deadline = 'Cette semaine';
        } elseif (str_contains($text, 'mois') || str_contains($text, 'weeks')) {
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

    private static function evaluateJob(array $analysis, array $constraints, array $freelancer): array
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
        $explanation = 'Mission intéressante mais à surveiller : certains points méritent clarification.';

        if ($score >= 2) {
            $grade = 'A';
            $explanation = sprintf(
                'Mission très intéressante pour %s : budget aligné et signaux au vert.',
                $freelancer['brand']
            );
        } elseif ($score < 0) {
            $grade = 'C';
            $explanation = 'Mission à éviter dans l’état actuel : budget faible ou signaux d’alerte.';
        }

        return [
            'grade' => $grade,
            'score' => $score,
            'explanation' => $explanation,
        ];
    }

    private static function generatePitch(array $job, array $analysis, array $evaluation, array $freelancer): string
    {
        $platformKey = self::normalizePlatform($job['platform'] ?? '');
        $platformMeta = self::platformMetadata()[$platformKey] ?? null;
        $language = strtolower($job['language'] ?? 'fr');
        $goal = $analysis['goal'];
        $techList = !empty($analysis['technologies'])
            ? implode(', ', $analysis['technologies'])
            : 'une stack web moderne (PHP, JS, APIs, automatisation)';
        $grade = $evaluation['grade'];
        $isTopPlatform = self::isTopPlatform($platformKey, $freelancer['top_platforms'] ?? []);

        if ($language === 'fr') {
            return self::generatePitchFr($platformMeta, $goal, $techList, $grade, $freelancer, $isTopPlatform);
        }

        return self::generatePitchEn($platformMeta, $goal, $techList, $grade, $freelancer, $isTopPlatform);
    }

    private static function generatePitchFr(?array $platformMeta, string $goal, string $techList, string $grade, array $freelancer, bool $isTopPlatform): string
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

    private static function generatePitchEn(?array $platformMeta, string $goal, string $techList, string $grade, array $freelancer, bool $isTopPlatform): string
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

    private static function platformMetadata(): array
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
                'notes' => 'Parfait pour offres packages et revenus semi-passifs.'
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

    private static function routines(): array
    {
        return [
            'daily' => [
                'title' => 'Routine 20 min / jour',
                'steps' => [
                    '5 min — Vérifier Codeur, Malt, Upwork, LinkedIn inbox',
                    '10 min — Répondre à 3-5 missions qualifiées (ouverture personnalisée)',
                    '5 min — Publier un micro-post (LinkedIn, IG story, devlog)',
                ],
            ],
            'boost' => [
                'title' => 'Routine boost 48h',
                'days' => [
                    [
                        'label' => 'Jour 1',
                        'actions' => [
                            '20 candidatures premium (Malt/Upwork)',
                            'Création d’un nouveau gig Fiverr',
                            '3 commentaires LinkedIn pertinents',
                            '1 mini-post technique',
                        ],
                    ],
                    [
                        'label' => 'Jour 2',
                        'actions' => [
                            '10 candidatures Codeur',
                            'Ajout d’un élément au portfolio',
                            '5 DM ciblés à des prospects',
                            'Optimisation d’un gig Fiverr + promo interne',
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function comparativeTable(): array
    {
        return [
            [
                'platform' => 'Malt',
                'difficulty' => '★★★',
                'earnings' => '★★★★★',
                'speed' => '★★★',
                'competition' => '★★',
                'ideal_for' => 'Dev, consulting',
                'notes' => 'Clients FR/UE premium',
            ],
            [
                'platform' => 'Upwork',
                'difficulty' => '★★★',
                'earnings' => '★★★★★',
                'speed' => '★★★',
                'competition' => '★★★★',
                'ideal_for' => 'Dev, AI, automation',
                'notes' => 'Très rentable si profil optimisé',
            ],
            [
                'platform' => 'Fiverr',
                'difficulty' => '★★',
                'earnings' => '★★★★',
                'speed' => '★★★★★',
                'competition' => '★★★',
                'ideal_for' => 'Offres packagées',
                'notes' => 'Bon pour revenu récurrent',
            ],
            [
                'platform' => 'Codeur',
                'difficulty' => '★★',
                'earnings' => '★★★',
                'speed' => '★★★',
                'competition' => '★★★★★',
                'ideal_for' => 'Quick wins, premiers avis',
                'notes' => 'Volume élevé, besoin pitch solide',
            ],
            [
                'platform' => 'FreelanceRepublik',
                'difficulty' => '★★★★',
                'earnings' => '★★★★★',
                'speed' => '★★★',
                'competition' => '★★',
                'ideal_for' => 'Dev senior remote',
                'notes' => 'Sélection stricte, clients qualifiés',
            ],
            [
                'platform' => 'ComeUp',
                'difficulty' => '★★',
                'earnings' => '★★',
                'speed' => '★★★★',
                'competition' => '★★★',
                'ideal_for' => 'Micro services',
                'notes' => 'Idéal pour démarrer et visibilité',
            ],
            [
                'platform' => 'Freelancer.com',
                'difficulty' => '★★★',
                'earnings' => '★★★',
                'speed' => '★★',
                'competition' => '★★★★★',
                'ideal_for' => 'Mix projets',
                'notes' => 'Budgets variables, filtrage nécessaire',
            ],
            [
                'platform' => 'Graphiste.com',
                'difficulty' => '★',
                'earnings' => '★★',
                'speed' => '★★★',
                'competition' => '★★',
                'ideal_for' => 'Design',
                'notes' => 'Parfait pour offres créatives',
            ],
            [
                'platform' => 'Redacteur.com',
                'difficulty' => '★',
                'earnings' => '★★',
                'speed' => '★★★',
                'competition' => '★★★',
                'ideal_for' => 'Rédaction SEO',
                'notes' => 'Complément contenu',
            ],
            [
                'platform' => 'Traduc.com',
                'difficulty' => '★',
                'earnings' => '★★',
                'speed' => '★★★',
                'competition' => '★★',
                'ideal_for' => 'Traduction',
                'notes' => 'Marché spécialisé',
            ],
        ];
    }
}
