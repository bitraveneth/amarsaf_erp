<?php

namespace App\Services\Assistant;

use App\Models\User;
use App\Support\Assistant\AssistantFaqCatalog;
use App\Support\Learning\LearningCourseBuilder;
use App\Support\Learning\LearningGlossary;
use App\Support\Learning\LearningHubRepository;
use Illuminate\Http\Request;

class LearningAssistantService
{
    protected ?array $searchIndex = null;

    /**
     * Module slug => searchable aliases (longest match wins in resolveModuleSlug).
     *
     * @var array<string, array<int, string>>
     */
    protected array $moduleAliases = [
        'overview' => ['whole system', 'end to end', 'erp flow', 'full process', 'overview'],
        'products' => ['product', 'products', 'material', 'materials', 'sku', 'price list', 'tax class'],
        'agents' => ['agent', 'agents', 'distributor', 'commission', 'zone', 'credit limit'],
        'procurement' => ['procurement', 'purchase order', 'purchase orders', 'po', 'grn', 'goods receipt', 'supplier', 'buying', 'three way match'],
        'warehouses' => ['warehouse', 'warehouses', 'depot', 'factory warehouse', 'delivery route', 'vehicle', 'location', 'bin'],
        'manufacturing' => ['manufacturing', 'production', 'bom', 'bill of materials', 'batch', 'production run', 'qc', 'pending receipts', 'confirm stock'],
        'inventory' => ['inventory', 'stock', 'transfer', 'low stock', 'mrp', 'stock movement'],
        'sales' => ['sales', 'sales order', 'sales orders', 'agent order', 'confirm order', 'picking'],
        'delivery' => ['delivery', 'deliveries', 'pod', 'proof of delivery', 'dispatch', 'vehicle load', 'packing slip', 'pick list'],
        'accounting' => ['accounting', 'invoice', 'receipt', 'journal', 'vat', 'supplier bill', 'payroll', 'cogs', 'gl'],
        'profit-loss' => ['profit and loss', 'profit loss', 'p and l', 'p&l', 'margin'],
        'reports' => ['reports', 'reporting', 'analytics', 'trial balance', 'aging', 'export center'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function contextFromRequest(Request $request): array
    {
        $context = $request->input('context', []);

        if (! is_array($context)) {
            $context = [];
        }

        $onLearningHub = $request->routeIs('admin.learning-hub');

        return [
            'source' => ($context['source'] ?? null) === 'learning-hub' || $onLearningHub
                ? 'learning-hub'
                : 'erp',
            'module_slug' => self::nullableString($context['module_slug'] ?? $request->query('module')),
            'lesson_index' => isset($context['lesson_index']) && is_numeric($context['lesson_index'])
                ? (int) $context['lesson_index']
                : null,
            'locale' => ($context['locale'] ?? 'en') === 'bn' ? 'bn' : 'en',
            'role' => self::nullableString($context['role'] ?? '') ?? 'all',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tryAnswer(User $user, string $message, array $context): ?array
    {
        $normalized = $this->normalize($message);
        if ($normalized === '') {
            return null;
        }

        if (AssistantIntentRouter::isBusinessQuestion($message)) {
            return null;
        }

        $locale = $context['locale'] ?? 'en';
        $inAcademy = ($context['source'] ?? 'erp') === 'learning-hub';

        if ($this->matchesExplainLesson($normalized)) {
            return $this->wrap(
                $user,
                $context,
                $this->explainCurrentLesson($context, $locale),
                $this->linksForContext($context, $locale)
            );
        }

        $processReply = $this->tryProcessFlowAnswer($user, $context, $normalized, $locale);
        if ($processReply !== null) {
            return $processReply;
        }

        if ($this->matchesRecommendPath($normalized)) {
            return $this->wrap(
                $user,
                $context,
                $this->recommendPathReply($user, $context, $locale),
                [$this->learningHubLink($locale)]
            );
        }

        if ($this->matchesUnifiedCapabilities($normalized)) {
            return $this->wrap(
                $user,
                $context,
                $this->unifiedCapabilitiesReply($locale),
                [$this->learningHubLink($locale)]
            );
        }

        if ($this->matchesLearningHubNav($normalized)) {
            return $this->wrap(
                $user,
                $context,
                $locale === 'bn'
                    ? 'ERP Academy (Learning Hub) সাইডবার বা Control মেনু থেকে খুলুন। সেখানে ধাপে ধাপে কোর্স, কুইজ ও গ্লসারি আছে।'
                    : 'Open ERP Academy (Learning Hub) from the sidebar or Control menu. It has step-by-step courses, quizzes, and glossaries.',
                [$this->learningHubLink($locale)]
            );
        }

        $match = $this->search($normalized, $context);
        $threshold = $inAcademy ? 10 : 16;

        if ($match !== null && ($match['score'] ?? 0) >= $threshold) {
            return $this->wrap(
                $user,
                $context,
                $match['answer'],
                $match['links']
            );
        }

        return null;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function quickAsks(array $context): array
    {
        $locale = $context['locale'] ?? 'en';
        $hasLesson = ($context['module_slug'] ?? null) && $context['lesson_index'] !== null;

        $asks = [];

        if ($hasLesson) {
            $asks[] = [
                'label' => $locale === 'bn' ? 'এই ধাপ ব্যাখ্যা' : 'Explain this lesson',
                'message' => $locale === 'bn' ? 'এই ধাপটি ব্যাখ্যা করুন' : 'Explain this lesson',
            ];
        }

        return array_merge($asks, [
            [
                'label' => $locale === 'bn' ? 'আজকের কাজ' : 'My tasks today',
                'message' => $locale === 'bn' ? 'আজ কী কাজ বাকি?' : 'What tasks need attention today?',
            ],
            [
                'label' => $locale === 'bn' ? 'BOM প্রক্রিয়া' : 'How BOM works',
                'message' => $locale === 'bn' ? 'BOM কীভাবে কাজ করে?' : 'How does BOM work?',
            ],
            [
                'label' => $locale === 'bn' ? 'GRN কী?' : 'What is GRN?',
                'message' => $locale === 'bn' ? 'GRN কী?' : 'What is GRN?',
            ],
            [
                'label' => $locale === 'bn' ? 'PO → GRN' : 'PO to GRN flow',
                'message' => $locale === 'bn' ? 'PO থেকে GRN প্রক্রিয়া' : 'What is the process from PO to GRN?',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function tryProcessFlowAnswer(User $user, array $context, string $normalized, string $locale): ?array
    {
        if (! $this->looksLikeProcessOrHowQuestion($normalized)) {
            return null;
        }

        $slug = $this->resolveModuleSlug($normalized) ?? ($context['module_slug'] ?? null);

        if ($slug === null && $this->looksLikeWholeSystemFlow($normalized)) {
            $slug = 'overview';
        }

        if ($slug === null) {
            return null;
        }

        $module = $this->module($slug);
        if (! $module) {
            return null;
        }

        $links = [$this->courseLink($slug, $locale)];
        $firstPath = $module['steps'][0]['path'] ?? null;
        if ($firstPath) {
            $links[] = $this->screenLink($firstPath, $locale);
        }

        return $this->wrap(
            $user,
            $context,
            $this->buildProcessFlowAnswer($module, $locale),
            $links
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function search(string $normalized, array $context): ?array
    {
        $locale = $context['locale'] ?? 'en';
        $resolvedSlug = $this->resolveModuleSlug($normalized);
        $best = null;
        $bestScore = 0;

        foreach ($this->index() as $entry) {
            $score = $this->scoreEntry($normalized, $entry, $context, $resolvedSlug);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }
        }

        if ($best === null || $bestScore < 8) {
            return null;
        }

        $answer = $this->pick($best['answer'] ?? [], $locale);
        if ($locale === 'bn' && ! empty($best['prefix_bn'])) {
            $answer = $best['prefix_bn'] . $answer;
        } elseif (! empty($best['prefix'])) {
            $answer = $best['prefix'] . $answer;
        }

        $links = [];

        if (! empty($best['module_slug'])) {
            $links[] = $this->courseLink($best['module_slug'], $locale, $best['lesson_index'] ?? null);
        }

        if (! empty($best['path'])) {
            $links[] = $this->screenLink($best['path'], $locale);
        }

        return [
            'score' => $bestScore,
            'answer' => $answer,
            'links' => array_values(array_filter($links)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function index(): array
    {
        if ($this->searchIndex !== null) {
            return $this->searchIndex;
        }

        $entries = [];

        foreach (LearningHubRepository::modules() as $module) {
            $slug = $module['slug'] ?? '';
            if ($slug === '') {
                continue;
            }

            $aliases = $this->moduleAliases[$slug] ?? [$slug];
            $titleEn = $this->pick($module['title'] ?? [], 'en');
            $titleBn = $this->pick($module['title'] ?? [], 'bn');
            $summaryEn = $this->pick($module['summary'] ?? [], 'en');
            $summaryBn = $this->pick($module['summary'] ?? [], 'bn');

            $entries[] = [
                'kind' => 'summary',
                'keywords' => $this->keywordsFromText(...array_merge([$titleEn, $summaryEn], $aliases)),
                'answer' => $module['summary'] ?? ['en' => '', 'bn' => ''],
                'module_slug' => $slug,
                'lesson_index' => 0,
            ];

            $processAnswer = [
                'en' => $this->buildProcessFlowAnswer($module, 'en'),
                'bn' => $this->buildProcessFlowAnswer($module, 'bn'),
            ];

            $entries[] = [
                'kind' => 'process_flow',
                'keywords' => $this->keywordsFromText(...array_merge([
                    $titleEn,
                    $summaryEn,
                    'process flow',
                    'workflow',
                    'how it works',
                    'how does it work',
                    'steps',
                    'procedure',
                    'process of ' . $slug,
                    'flow of ' . $slug,
                ], $aliases)),
                'answer' => $processAnswer,
                'module_slug' => $slug,
                'lesson_index' => 1,
            ];

            foreach ($aliases as $alias) {
                $entries[] = [
                    'kind' => 'how_works',
                    'keywords' => $this->keywordsFromText(
                        $alias,
                        'how ' . $alias . ' works',
                        'how does ' . $alias . ' work',
                        'process of ' . $alias,
                        'what is the process of ' . $alias,
                        $alias . ' process',
                        $alias . ' flow'
                    ),
                    'answer' => $processAnswer,
                    'module_slug' => $slug,
                ];
            }

            foreach ($module['steps'] ?? [] as $index => $step) {
                $stepTitleEn = $this->pick($step['title'] ?? [], 'en');
                $entries[] = [
                    'kind' => 'step',
                    'keywords' => $this->keywordsFromText(...array_merge([
                        $stepTitleEn,
                        $this->pick($step['body'] ?? [], 'en'),
                        $step['path'] ?? '',
                    ], $aliases)),
                    'answer' => $step['body'] ?? ['en' => '', 'bn' => ''],
                    'module_slug' => $slug,
                    'lesson_index' => $index + 1,
                    'path' => $step['path'] ?? null,
                    'prefix' => $stepTitleEn . ': ',
                    'prefix_bn' => $this->pick($step['title'] ?? [], 'bn') . ': ',
                ];
            }

            foreach ($module['tips'] ?? [] as $tip) {
                $text = is_array($tip) ? ($tip['en'] ?? $tip['bn'] ?? '') : (string) $tip;
                $textBn = is_array($tip) ? ($tip['bn'] ?? $tip['en'] ?? '') : (string) $tip;
                $entries[] = [
                    'kind' => 'tip',
                    'keywords' => $this->keywordsFromText(...array_merge([$text], $aliases)),
                    'answer' => ['en' => 'Tip: ' . $text, 'bn' => 'টিপ: ' . $textBn],
                    'module_slug' => $slug,
                ];
            }

            foreach ($module['examples'] ?? [] as $example) {
                $exampleTitle = $this->pick($example['title'] ?? [], 'en');
                $entries[] = [
                    'kind' => 'example',
                    'keywords' => $this->keywordsFromText(...array_merge([
                        $exampleTitle,
                        $this->pick($example['body'] ?? [], 'en'),
                    ], $aliases)),
                    'answer' => $example['body'] ?? ['en' => '', 'bn' => ''],
                    'module_slug' => $slug,
                    'prefix' => 'Example — ' . $exampleTitle . ': ',
                    'prefix_bn' => 'উদাহরণ — ' . $this->pick($example['title'] ?? [], 'bn') . ': ',
                ];
            }

            foreach ($module['faqs'] ?? [] as $faq) {
                $question = $this->pick($faq['q'] ?? [], 'en');
                $entries[] = [
                    'kind' => 'faq',
                    'keywords' => $this->keywordsFromText(...array_merge([
                        $question,
                        $this->pick($faq['a'] ?? [], 'en'),
                    ], $aliases)),
                    'answer' => $faq['a'] ?? ['en' => '', 'bn' => ''],
                    'module_slug' => $slug,
                ];
            }

            foreach (LearningGlossary::terms($module) as $term) {
                $termEn = $this->pick($term['term'] ?? [], 'en');
                $termBn = $this->pick($term['term'] ?? [], 'bn');
                $defEn = $this->pick($term['def'] ?? [], 'en');
                $entries[] = [
                    'kind' => 'glossary',
                    'keywords' => $this->keywordsFromText(...array_merge([
                        $termEn,
                        $termBn,
                        $defEn,
                        'what is ' . $termEn,
                        'what are ' . $termEn,
                        'meaning of ' . $termEn,
                        'define ' . $termEn,
                        'explain ' . $termEn,
                        $termEn . ' means',
                    ], $aliases)),
                    'answer' => [
                        'en' => $termEn . ' — ' . $defEn,
                        'bn' => $termBn . ' — ' . $this->pick($term['def'] ?? [], 'bn'),
                    ],
                    'module_slug' => $slug,
                ];
            }
        }

        $this->searchIndex = $entries;

        return $this->searchIndex;
    }

    protected function scoreEntry(string $normalized, array $entry, array $context, ?string $resolvedSlug): int
    {
        if (AssistantIntentRouter::isBusinessQuestion($normalized)) {
            return 0;
        }

        $score = 0;

        foreach ($entry['keywords'] as $keyword) {
            $keyword = $this->normalize($keyword);
            if ($keyword === '') {
                continue;
            }

            if ($normalized === $keyword) {
                $score += 100;
            } elseif (str_contains($normalized, $keyword)) {
                $score += 16 + min(34, strlen($keyword));
            } else {
                $score += $this->scoreTokenOverlap($normalized, $keyword);
            }
        }

        $entrySlug = $entry['module_slug'] ?? null;

        if ($resolvedSlug !== null && $entrySlug === $resolvedSlug) {
            $score += 45;
        }

        if (($context['module_slug'] ?? null) === $entrySlug) {
            $score += 10;
        }

        $kind = $entry['kind'] ?? '';

        if ($kind === 'glossary' && $this->looksLikeDefinitionQuestion($normalized)) {
            $score += 25;
        }

        if (in_array($kind, ['process_flow', 'how_works'], true) && $this->looksLikeProcessOrHowQuestion($normalized)) {
            $score += 30;
        }

        return $score;
    }

    protected function scoreTokenOverlap(string $normalized, string $keyword): int
    {
        $keywordTokens = array_values(array_filter(explode(' ', $keyword)));

        if (count($keywordTokens) < 2) {
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
            return 80;
        }

        if ($ratio >= 0.66) {
            return 48;
        }

        return 0;
    }

    protected function resolveModuleSlug(string $normalized): ?string
    {
        $bestSlug = null;
        $bestLength = 0;

        foreach ($this->moduleAliases as $slug => $aliases) {
            foreach ($aliases as $alias) {
                $aliasNorm = $this->normalize($alias);
                if ($aliasNorm === '') {
                    continue;
                }

                if (str_contains($normalized, $aliasNorm) && strlen($aliasNorm) > $bestLength) {
                    $bestLength = strlen($aliasNorm);
                    $bestSlug = $slug;
                }
            }

            if (str_contains($normalized, $slug) && strlen($slug) > $bestLength) {
                $bestLength = strlen($slug);
                $bestSlug = $slug;
            }
        }

        return $bestSlug;
    }

    protected function buildProcessFlowAnswer(array $module, string $locale): string
    {
        $title = $this->pick($module['title'] ?? [], $locale);
        $summary = $this->pick($module['summary'] ?? [], $locale);
        $flowLabels = $this->flowchartLabels($module['flowchart'] ?? [], $locale);

        $lines = [
            $locale === 'bn' ? "«{$title}» — প্রক্রিয়া:" : "Process — {$title}:",
            $summary,
        ];

        if ($flowLabels !== []) {
            $lines[] = '';
            $lines[] = $locale === 'bn' ? 'ধারা:' : 'Flow:';
            $lines[] = implode(' → ', $flowLabels);
        }

        $steps = $module['steps'] ?? [];
        if ($steps !== []) {
            $lines[] = '';
            $lines[] = $locale === 'bn' ? 'ধাপ:' : 'Steps:';
            foreach ($steps as $index => $step) {
                $stepTitle = $this->pick($step['title'] ?? [], $locale);
                $stepBody = $this->pick($step['body'] ?? [], $locale);
                $lines[] = ($index + 1) . '. ' . $stepTitle . ' — ' . $stepBody;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, string>|array<string, mixed>  $flowchart
     * @return array<int, string>
     */
    protected function flowchartLabels(array $flowchart, string $locale): array
    {
        $source = $flowchart[$locale] ?? $flowchart['en'] ?? '';
        if (! is_string($source) || $source === '') {
            return [];
        }

        preg_match_all('/\["([^"]+)"\]|\\"([^\\"]+)\\"/', $source, $matches);

        $labels = [];
        foreach ($matches[1] as $index => $label) {
            $labels[] = $label !== '' ? $label : ($matches[2][$index] ?? '');
        }

        return array_values(array_filter($labels));
    }

    protected function looksLikeProcessOrHowQuestion(string $normalized): bool
    {
        return preg_match(
            '/\b(process flow|process of|flow of|workflow|how does|how do|how .+ works|how .+ work|steps for|procedure|what is the process|what s the process)\b/u',
            $normalized
        ) === 1;
    }

    protected function looksLikeDefinitionQuestion(string $normalized): bool
    {
        return preg_match('/\b(what is|what are|meaning of|define|explain|stands for|means)\b/u', $normalized) === 1;
    }

    protected function looksLikeWholeSystemFlow(string $normalized): bool
    {
        return str_contains($normalized, 'whole system')
            || str_contains($normalized, 'entire erp')
            || str_contains($normalized, 'full process')
            || str_contains($normalized, 'end to end');
    }

    protected function explainCurrentLesson(array $context, string $locale): string
    {
        $slug = $context['module_slug'] ?? null;
        if (! $slug) {
            return $locale === 'bn'
                ? 'এখন কোনো কোর্স খোলা নেই। ERP Academy থেকে একটি কোর্স বেছে নিন, তারপর আবার জিজ্ঞেস করুন।'
                : 'No course is open right now. Pick a course in ERP Academy, then ask again.';
        }

        $module = $this->module($slug);
        if (! $module) {
            return $locale === 'bn' ? 'কোর্স খুঁজে পাচ্ছি না।' : 'I could not find that course.';
        }

        $lessons = LearningCourseBuilder::lessons($module);
        $index = max(0, (int) ($context['lesson_index'] ?? 0));
        $lesson = $lessons[$index] ?? null;

        if (! $lesson) {
            return $this->buildProcessFlowAnswer($module, $locale);
        }

        $title = $this->pick($lesson['title'] ?? [], $locale);
        $type = $lesson['type'] ?? '';

        if ($type === 'step') {
            $body = $this->pick($lesson['body'] ?? [], $locale);
            $pathHint = ! empty($lesson['path'])
                ? ($locale === 'bn' ? ' ERP-তে খুলতে নিচের লিংক ব্যবহার করুন।' : ' Use the link below to open this screen in the ERP.')
                : '';

            return "{$title}. {$body}{$pathHint}";
        }

        if ($type === 'flow') {
            return $this->buildProcessFlowAnswer($module, $locale);
        }

        return $this->pick($module['summary'] ?? [], $locale);
    }

    protected function recommendPathReply(User $user, array $context, string $locale): string
    {
        $role = $context['role'] ?? 'all';
        if ($role === 'all' || $role === '') {
            $role = LearningHubRepository::defaultRoleFilter($user->role);
        }

        $path = LearningHubRepository::learningPath($role !== 'all' ? $role : $user->role);
        $modules = collect(LearningHubRepository::modules())->keyBy('slug');

        if ($path === []) {
            return $locale === 'bn'
                ? 'ERP Academy-তে «How the whole system works» দিয়ে শুরু করুন, তারপর আপনার কাজের কোর্স বেছে নিন।'
                : 'Start with «How the whole system works» in ERP Academy, then pick courses for your role.';
        }

        $lines = [$locale === 'bn' ? 'আপনার জন্য সুপারিশকৃত ক্রম:' : 'Recommended order for you:'];
        $position = 1;

        foreach ($path as $slug) {
            $module = $modules->get($slug);
            if (! $module) {
                continue;
            }
            $lines[] = "{$position}. " . $this->pick($module['title'] ?? [], $locale);
            $position++;
        }

        return implode("\n", $lines);
    }

    protected function unifiedCapabilitiesReply(string $locale): string
    {
        return $locale === 'bn'
            ? "আমি Saf AI Assistant — ERP Academy ও লাইভ ব্যবসার ডেটা একসাথে। জিজ্ঞেস করুন:\n• আজ কী কাজ বাকি / রাজস্ব / স্টক\n• BOM, GRN, POD প্রক্রিয়া বা অর্থ কী\n• কোন কোর্স নেবেন, কোন স্ক্রিনে যাবেন"
            : "I'm Saf AI Assistant — ERP training and live business data together. Ask about:\n• Tasks today, revenue, stock, receivables\n• Process flows (BOM, GRN, POD, sales, accounting)\n• What terms mean and where to click in the ERP";
    }

    protected function matchesUnifiedCapabilities(string $normalized): bool
    {
        if (str_contains($normalized, 'what can you teach')
            || str_contains($normalized, 'help me learn')
            || str_contains($normalized, 'learning mode')
            || str_contains($normalized, 'tutor')) {
            return true;
        }

        return (str_contains($normalized, 'what can you do')
                || str_contains($normalized, 'what you can do')
                || str_contains($normalized, 'how can you help'))
            && (str_contains($normalized, 'learn')
                || str_contains($normalized, 'course')
                || str_contains($normalized, 'training')
                || str_contains($normalized, 'erp academy'));
    }

    protected function matchesExplainLesson(string $normalized): bool
    {
        foreach ([
            'explain this lesson', 'explain this step', 'explain this course',
            'what is this lesson', 'what is this step',
            'explain lesson', 'explain step',
            'এই ধাপ', 'এই লেসন', 'ব্যাখ্যা কর', 'ব্যাখ্যা করুন',
        ] as $phrase) {
            if (str_contains($normalized, $this->normalize($phrase))) {
                return true;
            }
        }

        return false;
    }

    protected function matchesRecommendPath(string $normalized): bool
    {
        return str_contains($normalized, 'which course')
            || str_contains($normalized, 'what course')
            || str_contains($normalized, 'recommend')
            || str_contains($normalized, 'onboarding')
            || str_contains($normalized, 'where should i start')
            || str_contains($normalized, 'কোন কোর্স')
            || str_contains($normalized, 'কোর্স নেব');
    }

    protected function matchesLearningHubNav(string $normalized): bool
    {
        return str_contains($normalized, 'learning hub')
            || str_contains($normalized, 'erp academy')
            || str_contains($normalized, 'training center')
            || str_contains($normalized, 'শেখার কেন্দ্র');
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function linksForContext(array $context, string $locale): array
    {
        $links = [];

        if ($context['module_slug'] ?? null) {
            $links[] = $this->courseLink(
                $context['module_slug'],
                $locale,
                $context['lesson_index'] ?? null
            );
        }

        $module = $this->module($context['module_slug'] ?? null);
        $lessons = $module ? LearningCourseBuilder::lessons($module) : [];
        $lesson = $lessons[$context['lesson_index'] ?? 0] ?? null;

        if (! empty($lesson['path'])) {
            $links[] = $this->screenLink($lesson['path'], $locale);
        }

        return array_values(array_filter($links));
    }

    protected function courseLink(string $slug, string $locale, ?int $lessonIndex = null): array
    {
        return [
            'label' => $locale === 'bn' ? 'কোর্স খুলুন' : 'Open course',
            'url' => route('admin.learning-hub', ['module' => $slug]),
        ];
    }

    protected function screenLink(string $path, string $locale): array
    {
        return [
            'label' => $locale === 'bn' ? 'ERP-তে খুলুন' : 'Open in ERP',
            'url' => $path,
        ];
    }

    protected function learningHubLink(string $locale): array
    {
        return [
            'label' => 'ERP Academy',
            'url' => route('admin.learning-hub'),
        ];
    }

    protected function module(?string $slug): ?array
    {
        if (! $slug) {
            return null;
        }

        return LearningHubRepository::module($slug);
    }

    /**
     * @param  array<string, string>  $pair
     */
    protected function pick(array $pair, string $locale): string
    {
        if ($locale === 'bn') {
            return trim($pair['bn'] ?? $pair['en'] ?? '');
        }

        return trim($pair['en'] ?? $pair['bn'] ?? '');
    }

    /**
     * @return array<int, string>
     */
    protected function keywordsFromText(string ...$parts): array
    {
        $keywords = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $keywords[] = $part;
            $normalized = $this->normalize($part);

            if ($normalized !== '' && $normalized !== $part) {
                $keywords[] = $normalized;
            }

            foreach (preg_split('/\s+/u', $normalized) ?: [] as $token) {
                if (strlen($token) >= 2) {
                    $keywords[] = $token;
                }
            }
        }

        return array_values(array_unique(array_filter($keywords)));
    }

    protected function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s\?]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * @param  array<int, array<string, string>>  $links
     * @return array<string, mixed>
     */
    protected function wrap(User $user, array $context, string $reply, array $links): array
    {
        return [
            'reply' => $reply,
            'links' => array_values(array_filter($links)),
            'suggestions' => AssistantFaqCatalog::quickAsks(),
        ];
    }

    protected static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
