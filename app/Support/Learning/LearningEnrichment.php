<?php

namespace App\Support\Learning;

class LearningEnrichment
{
    /**
     * Attach rich content blocks to each lesson.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function applyToLessons(array $module, array $lessons): array
    {
        $slug = $module['slug'] ?? '';
        $custom = self::load($slug);

        return collect($lessons)
            ->map(function (array $lesson) use ($module, $custom) {
                $id = $lesson['id'] ?? '';
                $blocks = $custom[$id] ?? null;

                if ($blocks === null) {
                    $blocks = self::autoBlocks($module, $lesson);
                }

                if (! empty($blocks)) {
                    $lesson['blocks'] = $blocks;
                }

                if (($lesson['type'] ?? '') === 'flow') {
                    $lesson['blocks'] = $lesson['blocks'] ?? [];
                    $hasDiagram = collect($lesson['blocks'])->contains(fn ($b) => ($b['type'] ?? '') === 'diagram');
                    if (! $hasDiagram && ! empty($module['flowchart'])) {
                        array_unshift($lesson['blocks'], [
                            'type' => 'diagram',
                            'source' => $module['flowchart'],
                        ]);
                    }

                    $pipeline = LearningPipeline::forLesson($module, $custom);
                    $lesson['pipeline'] = $pipeline;
                    $pipelineBlock = [
                        'type' => 'pipeline',
                        'title' => [
                            'en' => 'Process at a glance',
                            'bn' => 'এক নজরে প্রক্রিয়া',
                        ],
                        'steps' => $pipeline['steps'] ?? [],
                    ];
                    $lesson['blocks'] = array_merge([$pipelineBlock], $lesson['blocks'] ?? []);
                }

                return $lesson;
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function load(string $slug): array
    {
        $path = resource_path('learning/enrichments/' . $slug . '.php');

        if (! file_exists($path)) {
            return [];
        }

        return require $path;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function autoBlocks(array $module, array $lesson): array
    {
        $type = $lesson['type'] ?? '';

        return match ($type) {
            'welcome' => self::welcomeBlocks($module),
            'flow' => self::flowBlocks($module),
            'step' => self::stepBlocks($lesson),
            'practice' => self::practiceBlocks($module),
            default => [],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function welcomeBlocks(array $module): array
    {
        $blocks = [
            [
                'type' => 'callout',
                'variant' => 'info',
                'title' => ['en' => 'What this course covers', 'bn' => 'এই কোর্সে যা আছে'],
                'body' => $module['summary'] ?? ['en' => '', 'bn' => ''],
            ],
        ];

        if (! empty($module['quick_start']['items'])) {
            $blocks[] = [
                'type' => 'timeline',
                'title' => $module['quick_start']['title'] ?? ['en' => 'Quick start', 'bn' => 'দ্রুত শুরু'],
                'items' => $module['quick_start']['items'],
            ];
        }

        if (! empty($module['onboarding_checklist']['items'])) {
            $blocks[] = [
                'type' => 'checklist',
                'title' => $module['onboarding_checklist']['title'] ?? ['en' => 'Before you go live', 'bn' => 'লাইভের আগে'],
                'items' => $module['onboarding_checklist']['items'],
            ];
        }

        return $blocks;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function flowBlocks(array $module): array
    {
        return [
            [
                'type' => 'diagram',
                'source' => $module['flowchart'] ?? ['en' => '', 'bn' => ''],
            ],
            [
                'type' => 'callout',
                'variant' => 'tip',
                'title' => ['en' => 'Read the flow left to right', 'bn' => 'বাম থেকে ডানে পড়ুন'],
                'body' => [
                    'en' => 'Each box is an ERP document or action. Follow arrows to see what happens next. Open the step lessons after this for click-by-click instructions.',
                    'bn' => 'প্রতিটি বক্স একটি ERP ডকুমেন্ট বা কাজ। তীর অনুসরণ করে পরের ধাপ দেখুন। ক্লিক-ধরে-ক্লিক নির্দেশনার জন্য পরের পাঠগুলো খুলুন।',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function stepBlocks(array $lesson): array
    {
        $path = $lesson['path'] ?? null;
        $screen = self::screenFromPath($path);

        $blocks = [
            [
                'type' => 'callout',
                'variant' => 'goal',
                'title' => ['en' => 'Goal of this step', 'bn' => 'এই ধাপের লক্ষ্য'],
                'body' => $lesson['body'] ?? ['en' => '', 'bn' => ''],
            ],
        ];

        if ($screen !== null) {
            $blocks[] = ['type' => 'screen', ...$screen];
            $blocks[] = [
                'type' => 'clicks',
                'title' => ['en' => 'How to do it in the ERP', 'bn' => 'ERP-তে কীভাবে করবেন'],
                'items' => self::clicksFromScreen($screen),
            ];
        }

        if ($path) {
            $blocks[] = [
                'type' => 'callout',
                'variant' => 'action',
                'title' => ['en' => 'Try it yourself', 'bn' => 'নিজে চেষ্টা করুন'],
                'body' => [
                    'en' => 'Open the live screen and complete this step while following the walkthrough on the left.',
                    'bn' => 'লাইভ স্ক্রিন খুলে বামের ওয়াকথ্রু অনুসরণ করে এই ধাপ সম্পন্ন করুন।',
                ],
                'path' => $path,
            ];
        }

        return $blocks;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function practiceBlocks(array $module): array
    {
        $blocks = [];

        foreach ($module['examples'] ?? [] as $example) {
            $blocks[] = [
                'type' => 'example_card',
                'title' => $example['title'] ?? ['en' => 'Example', 'bn' => 'উদাহরণ'],
                'body' => $example['body'] ?? ['en' => '', 'bn' => ''],
            ];
        }

        if (! empty($module['tips'])) {
            $blocks[] = [
                'type' => 'tips',
                'title' => ['en' => 'Pro tips', 'bn' => 'গুরুত্বপূর্ণ টিপস'],
                'items' => $module['tips'],
            ];
        }

        return $blocks;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function screenFromPath(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        $map = self::screenMap();

        if (isset($map[$path])) {
            return $map[$path];
        }

        foreach ($map as $key => $screen) {
            if (str_starts_with($path, rtrim($key, '/'))) {
                return $screen;
            }
        }

        return [
            'title' => ['en' => 'ERP screen', 'bn' => 'ERP স্ক্রিন'],
            'menu' => ['en' => 'Use sidebar menu to navigate', 'bn' => 'সাইডবার মেনু দিয়ে যান'],
            'path' => $path,
            'highlights' => [
                ['label' => ['en' => 'Main list / form', 'bn' => 'মূল তালিকা / ফর্ম'], 'desc' => ['en' => 'Complete the fields for this step.', 'bn' => 'এই ধাপের ফিল্ড পূরণ করুন।']],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function clicksFromScreen(array $screen): array
    {
        $menu = $screen['menu'] ?? ['en' => '', 'bn' => ''];
        $items = [
            [
                'en' => 'Log in to Saf ERP and open the **sidebar menu** on the left.',
                'bn' => 'Saf ERP-এ লগ ইন করে **বাম সাইডবার মেনু** খুলুন।',
            ],
            [
                'en' => 'Navigate: **' . ($menu['en'] ?? '') . '**.',
                'bn' => 'যান: **' . ($menu['bn'] ?? $menu['en'] ?? '') . '**.',
            ],
        ];

        foreach ($screen['highlights'] ?? [] as $i => $highlight) {
            $label = $highlight['label']['en'] ?? '';
            $items[] = [
                'en' => 'Find **' . $label . '** — ' . ($highlight['desc']['en'] ?? ''),
                'bn' => '**' . ($highlight['label']['bn'] ?? $label) . '** খুঁজুন — ' . ($highlight['desc']['bn'] ?? ''),
            ];
        }

        $items[] = [
            'en' => 'Click **Save** or **Submit** when all required fields are filled. Check for green success message.',
                'bn' => 'প্রয়োজনীয় ফিল্ড পূরণ করার পর **Save** বা **Submit** ক্লিক করুন। সবুজ সাকসেস মেসেজ দেখুন।',
        ];

        return $items;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function screenMap(): array
    {
        return require resource_path('learning/screen-map.php');
    }
}
