<?php

namespace PdfFit\JobAgent;

use DateTimeImmutable;

/**
 * Agentic assistant that analyses job posts and generates a tailored roadmap
 * for Kevin Marville (Tech & Stream) before applying.
 */
final class JobApplicationAgent
{
    private const TECH_KEYWORDS = [
        'php', 'python', 'javascript', 'typescript', 'vue', 'react', 'symphony', 'symfony',
        'laravel', 'node', 'api', 'rest', 'graphql', 'microservice', 'ci/cd',
        'iot', 'microcontroller', 'esp32', 'robotics', 'aws', 'azure', 'gcp'
    ];

    private const CULTURE_KEYWORDS = [
        'remote', 'hybrid', 'on-site', 'startup', 'scale-up', 'enterprise',
        'agile', 'scrum', 'mission-driven', 'impact', 'sustainability',
        'international', 'french', 'english', 'bilingual'
    ];

    /**
     * Analyse raw job text and return a high-level summary.
     */
    public function analyse(string $text): array
    {
        $normalized = strtolower($text);

        return [
            'role'       => $this->extractRole($text),
            'company'    => $this->extractCompany($text),
            'technos'    => $this->extractKeywords($normalized, self::TECH_KEYWORDS),
            'culture'    => $this->extractKeywords($normalized, self::CULTURE_KEYWORDS),
            'budget'     => $this->extractBudget($text),
            'deadline'   => $this->extractDeadline($normalized),
            'language'   => $this->detectLanguage($normalized),
            'raw_excerpt'=> $this->firstLines($text),
        ];
    }

    /**
     * Generate a 10 step roadmap inspired by the "10 Things" video and
     * personalised for Kevin's Tech & Stream positioning.
     */
    public function generateRoadmap(array $analysis): array
    {
        $role = $analysis['role'] ?: 'the role';
        $company = $analysis['company'] ?: 'the target company';
        $technos = $analysis['technos'];
        $techStack = $technos ? implode(', ', $technos) : 'the stack highlighted in the job post';
        $deadline = $analysis['deadline'] ?: 'the expected timeline';

        return [
            [
                'title' => 'Clarify the target',
                'detail' => sprintf(
                    "Confirm that %s aligns with the Tech & Stream focus (web + embedded + content). " .
                    "Decide upfront if you want a mission, freelance contract, or long-term collaboration.",
                    $role
                ),
            ],
            [
                'title' => 'Refresh résumé & portfolio',
                'detail' => 'Update the PDF résumé, Malt/Upwork profiles, and the Tech & Stream site with the latest robotics, ESP32 '
                    . 'and API deliverables that map to the role.',
            ],
            [
                'title' => 'Deep company reconnaissance',
                'detail' => sprintf(
                    "Review %s: mission, tech choices, funding, and tone. Capture 2-3 hooks to reuse in your outreach.",
                    $company
                ),
            ],
            [
                'title' => 'Tailor the narrative',
                'detail' => sprintf(
                    "Mirror the vocabulary around %s. Build a 3-sentence elevator pitch that links the client's need to your " .
                    "full-stack + IoT experience.",
                    $techStack
                ),
            ],
            [
                'title' => 'Highlight unique value',
                'detail' => 'Position the hybrid profile: web lead + automation + content engine. Mention the robotics lab, '
                    . 'YouTube/Twitch ecosystem, and the ability to ship both front and firmware.',
            ],
            [
                'title' => 'Quantify achievements',
                'detail' => 'List metrics (conversion uplift, processing time saved, number of automations delivered). Prepare 2 quick case '
                    . 'studies that resonate with the job.',
            ],
            [
                'title' => 'Audit digital presence',
                'detail' => 'Ensure LinkedIn, GitHub, Tech & Stream blog and the indie hacker content all reflect the same positioning and '
                    . 'feature current projects.',
            ],
            [
                'title' => 'Culture & story fit',
                'detail' => sprintf(
                    "Draft a short story on why you thrive in environments that are %s and how collaboration works with you.",
                    $analysis['culture'] ? implode(', ', $analysis['culture']) : 'fast-paced, collaborative'
                ),
            ],
            [
                'title' => 'Prepare your “why”',
                'detail' => sprintf(
                    "Articulate why %s is exciting, why Kevin is the right force-multiplier now, and why the timing (%s) works.",
                    $company,
                    $deadline
                ),
            ],
            [
                'title' => 'Logistics & readiness',
                'detail' => 'Line up references, confirm availability, benchmark the day rate/fee, rehearse discovery call questions and '
                    . 'follow-up plan.',
            ],
        ];
    }

    /**
     * Render roadmap as markdown bullet list.
     */
    public function renderRoadmap(array $roadmap): string
    {
        $lines = [];
        foreach ($roadmap as $index => $step) {
            $lines[] = sprintf("%d. **%s** — %s", $index + 1, $step['title'], $step['detail']);
        }

        return implode(PHP_EOL, $lines);
    }

    private function extractRole(string $text): ?string
    {
        if (preg_match('/(?:seeking|searching for|looking for|recherchons|recherche)\s+(?:an?\s+)?([A-Za-zÀ-ÿ0-9\- ]{5,60})/i', $text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^\s*([A-Za-zÀ-ÿ0-9\- ]{5,60})(?:\s*-|\s*\n)/m', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function extractCompany(string $text): ?string
    {
        if (preg_match('/at\s+([A-Z][A-Za-z0-9& ]{2,50})/', $text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/chez\s+([A-Z][A-Za-z0-9& ]{2,50})/i', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function extractKeywords(string $normalized, array $keywords): array
    {
        $found = [];
        foreach ($keywords as $keyword) {
            if (str_contains($normalized, strtolower($keyword))) {
                $found[] = $keyword;
            }
        }

        return array_values(array_unique($found));
    }

    private function extractBudget(string $text): ?string
    {
        if (preg_match('/(\d+[\s\u00A0]?\d*)\s*(€|eur|euros|k)/i', $text, $m)) {
            $amount = str_replace([' ', '\u00A0'], '', $m[1]);
            $unit = strtoupper($m[2]);
            return $amount . ' ' . $unit;
        }

        return null;
    }

    private function extractDeadline(string $normalized): ?string
    {
        if (preg_match('/urgent|asap|immédiat|immédiate/', $normalized)) {
            return 'urgent';
        }
        if (preg_match('/\b(\d{1,2})\s*jours?/', $normalized, $m)) {
            return $m[1] . ' days';
        }
        if (preg_match('/\b(week|month|mois)/', $normalized, $m)) {
            return $m[1];
        }

        return null;
    }

    private function detectLanguage(string $normalized): string
    {
        $frenchTokens = ['bonjour', 'mission', 'cahier des charges', 'disponible'];
        $hits = 0;
        foreach ($frenchTokens as $token) {
            if (str_contains($normalized, $token)) {
                $hits++;
            }
        }

        return $hits >= 2 ? 'fr' : 'en';
    }

    private function firstLines(string $text, int $lines = 3): string
    {
        $parts = preg_split('/\r?\n/', trim($text));
        $excerpt = array_slice($parts, 0, $lines);
        return implode(' ', $excerpt);
    }
}
