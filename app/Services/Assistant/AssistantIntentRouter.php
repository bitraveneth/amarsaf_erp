<?php

namespace App\Services\Assistant;

use App\Support\Assistant\AssistantFaqCatalog;

class AssistantIntentRouter
{
    protected array $synonymMap = [
        'what_can_you_do' => ['what you can do', 'what can be done', 'what could you do', 'what can i ask', 'what do you do', 'what are your capabilities', 'what can this assistant do'],
        'who_are_you' => ['who are you', 'what are you', 'your name'],
        'joke_arif' => ['do you know arif', 'who is arif', 'know arif', 'about arif', 'who is the owner', 'who owns saf', 'saf owner', 'my boyfriend'],
        'revenue_mtd' => ['money we make', 'how much money', 'how much we earn', 'sales revenue', 'total sales'],
        'receivables' => ['debt', 'customer debt', 'money owed to us', 'outstanding balance'],
        'profit_estimate_mtd' => ['are we profitable', 'how much profit', 'net income'],
        'ops_tasks' => ['what should i do', 'anything pending', 'issues today'],
        'business_summary' => ['tell me everything', 'full update', 'dashboard summary'],
        'business_health' => ['are we ok', 'how is business', 'doing good'],
    ];

    public function route(string $message): array
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return ['type' => 'unknown', 'key' => null, 'confidence' => 0, 'entry' => null];
        }

        $best = null;
        $bestScore = 0;

        foreach (AssistantFaqCatalog::entries() as $entry) {
            $score = $this->scoreEntry($normalized, $entry);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }
        }

        foreach ($this->synonymMap as $intentKey => $phrases) {
            foreach ($phrases as $phrase) {
                if (str_contains($normalized, $this->normalize($phrase))) {
                    $entry = AssistantFaqCatalog::findById($intentKey)
                        ?? $this->findMetricEntry($intentKey);
                    if ($entry) {
                        $score = 50 + strlen($phrase);
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $best = $entry;
                        }
                    }
                }
            }
        }

        $capabilityEntry = $this->matchCapabilityIntent($normalized);
        if ($capabilityEntry) {
            $capabilityScore = 72;
            if ($capabilityScore > $bestScore) {
                $bestScore = $capabilityScore;
                $best = $capabilityEntry;
            }
        }

        if ($best === null || $bestScore < 8) {
            return ['type' => 'unknown', 'key' => null, 'confidence' => 0, 'entry' => null];
        }

        $type = $best['type'] ?? 'static';

        return [
            'type' => $type === 'metric' ? 'metric' : ($type === 'greeting' ? 'greeting' : 'faq'),
            'key' => $best['id'],
            'metric' => $best['metric'] ?? null,
            'confidence' => min(100, $bestScore),
            'entry' => $best,
        ];
    }

    protected function findMetricEntry(string $metricKey): ?array
    {
        foreach (AssistantFaqCatalog::entries() as $entry) {
            if (($entry['metric'] ?? null) === $metricKey) {
                return $entry;
            }
        }

        return null;
    }

    protected function scoreEntry(string $normalized, array $entry): int
    {
        $score = 0;

        foreach ($entry['keywords'] as $keyword) {
            $keyword = $this->normalize($keyword);
            if ($keyword === '') {
                continue;
            }

            if ($normalized === $keyword) {
                $score += 100;
            } elseif (str_contains($normalized, $keyword)) {
                $score += 20 + min(30, strlen($keyword));
            } else {
                $score += $this->scoreTokenOverlap($normalized, $keyword);
            }
        }

        return $score;
    }

    protected function scoreTokenOverlap(string $normalized, string $keyword): int
    {
        $keywordTokens = array_values(array_filter(explode(' ', $keyword)));

        if (count($keywordTokens) < 3) {
            return 0;
        }

        $messageTokens = array_flip(explode(' ', $normalized));
        $matched = 0;

        foreach ($keywordTokens as $token) {
            if (isset($messageTokens[$token])) {
                $matched++;
            }
        }

        $ratio = $matched / count($keywordTokens);

        if ($ratio >= 1) {
            return 85;
        }

        if ($ratio >= 0.75) {
            return 55;
        }

        return 0;
    }

    protected function matchCapabilityIntent(string $normalized): ?array
    {
        if ($this->looksLikeMetricQuestion($normalized)) {
            return null;
        }

        if (! preg_match('/\bwhat\b/u', $normalized)) {
            return null;
        }

        $hasAbility = str_contains($normalized, 'can')
            || str_contains($normalized, 'could')
            || str_contains($normalized, 'able')
            || str_contains($normalized, 'help');

        $hasAction = preg_match('/\b(do|done|help|know|ask|capable|capabilities)\b/u', $normalized) === 1;

        if ($hasAbility && $hasAction) {
            return AssistantFaqCatalog::findById('what_can_you_do');
        }

        return null;
    }

    protected function looksLikeMetricQuestion(string $normalized): bool
    {
        foreach ([
            'revenue', 'profit', 'order', 'stock', 'production', 'invoice',
            'payroll', 'expense', 'receivable', 'collection', 'delivery', 'target',
        ] as $term) {
            if (str_contains($normalized, $term)) {
                return true;
            }
        }

        return false;
    }

    protected function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s\?]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
