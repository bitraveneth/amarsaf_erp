<?php

namespace App\Services\Assistant;

use App\Support\Assistant\AssistantDataCatalog;
use App\Support\Assistant\AssistantFaqCatalog;

class AssistantIntentRouter
{
    protected array $synonymMap = [];

    public function __construct()
    {
        $this->synonymMap = AssistantDataCatalog::synonymMap();
    }

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

    public static function isBusinessQuestion(string $message): bool
    {
        $normalized = (new self)->normalize($message);

        if ($normalized === '') {
            return false;
        }

        if (self::looksLikeTrainingOnly($normalized)) {
            return false;
        }

        foreach (AssistantDataCatalog::businessSignals() as $signal) {
            if (strlen($signal) < 5 && ! str_contains($signal, ' ')) {
                continue;
            }
            if (str_contains($normalized, $signal)) {
                return true;
            }
        }

        foreach (['profit', 'revenue', 'task', 'tasks', 'qc', 'mtd', 'grn'] as $short) {
            if ($short === 'grn') {
                continue;
            }
            if (str_contains($normalized, $short)) {
                return true;
            }
        }

        if (preg_match('/\b(how much|how many)\b/u', $normalized)
            && preg_match('/\b(revenue|profit|sales|order|stock|receivable|outstanding|collection|production|agent|return|expense|payroll|invoice|task|delivery|target)\b/u', $normalized)) {
            return true;
        }

        return false;
    }

    protected static function looksLikeTrainingOnly(string $normalized): bool
    {
        if (preg_match(
            '/\b(process flow|process of|flow of|workflow|how does|how do|how .+ works|how .+ work|meaning of|define|explain|steps for|procedure|course|erp academy|learning hub)\b/u',
            $normalized
        )) {
            if (! preg_match('/\b(how much|how many|this month|today|mtd|outstanding|receivable|profit|revenue|summary|target progress|collection rate|need attention|pending)\b/u', $normalized)) {
                return true;
            }
        }

        if (preg_match('/\b(what is|what are)\b/u', $normalized)) {
            foreach (['grn', 'bom', 'pod', 'bill of materials', 'goods receipt', 'proof of delivery'] as $term) {
                if (str_contains($normalized, $term)) {
                    return true;
                }
            }

            if (! preg_match('/\b(profit|revenue|receivable|outstanding|mtd|collection rate|sales target)\b/u', $normalized)) {
                if (preg_match('/\b(what is|what are)\b/u', $normalized)
                    && ! preg_match('/\b(our|my|the|this month|today|outstanding|receivable)\b/u', $normalized)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @deprecated Use isBusinessQuestion() */
    public static function shouldPreferBusiness(string $message): bool
    {
        return self::isBusinessQuestion($message);
    }

    public static function looksLikeTrainingQuestion(string $normalized): bool
    {
        if (self::isBusinessQuestion($normalized)) {
            return false;
        }

        if (preg_match(
            '/\b(process flow|process of|flow of|workflow|how does|how do|how .+ works|how .+ work|what is|what are|meaning of|define|explain|steps for|procedure|course|erp academy|learning hub)\b/u',
            $normalized
        )) {
            return true;
        }

        foreach (['grn', 'bom', 'pod', 'po to grn', 'bill of materials'] as $term) {
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
