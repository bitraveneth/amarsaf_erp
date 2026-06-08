<?php

namespace App\Support\Learning;

class LearningPipeline
{
    /**
     * @return array<string, mixed>
     */
    public static function forLesson(array $module, array $custom): array
    {
        if (! empty($custom['flow_pipeline']['steps'])) {
            return $custom['flow_pipeline'];
        }

        $steps = collect($module['steps'] ?? [])
            ->map(function (array $step, int $index) use ($module) {
                return [
                    'label' => $step['title'] ?? ['en' => 'Step ' . ($index + 1), 'bn' => 'ধাপ ' . ($index + 1)],
                    'desc' => $step['body'] ?? ['en' => '', 'bn' => ''],
                    'phase' => self::phaseForSlug($module['slug'] ?? ''),
                    'path' => $step['path'] ?? null,
                ];
            })
            ->filter(fn (array $s) => self::hasText($s['label']))
            ->values()
            ->all();

        if (count($steps) >= 2) {
            return ['steps' => $steps];
        }

        return ['steps' => self::fallbackSteps($module)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function fallbackSteps(array $module): array
    {
        return [
            [
                'label' => ['en' => 'Open ERP screen', 'bn' => 'ERP স্ক্রিন খুলুন'],
                'desc' => $module['summary'] ?? ['en' => '', 'bn' => ''],
                'phase' => self::phaseForSlug($module['slug'] ?? ''),
            ],
            [
                'label' => ['en' => 'Complete actions', 'bn' => 'কাজ সম্পন্ন'],
                'desc' => ['en' => 'Follow the step lessons in this course.', 'bn' => 'এই কোর্সের ধাপ অনুসরণ করুন।'],
                'phase' => self::phaseForSlug($module['slug'] ?? ''),
            ],
            [
                'label' => ['en' => 'Verify result', 'bn' => 'ফলাফল যাচাই'],
                'desc' => ['en' => 'Check stock, status, or report matches expectation.', 'bn' => 'স্টক, স্ট্যাটাস বা রিপোর্ট যাচাই করুন।'],
                'phase' => self::phaseForSlug($module['slug'] ?? ''),
            ],
        ];
    }

    protected static function phaseForSlug(string $slug): string
    {
        return match ($slug) {
            'overview', 'products' => 'foundation',
            'agents', 'sales' => 'commercial',
            'accounting', 'profit-loss', 'reports' => 'finance',
            default => 'operations',
        };
    }

    protected static function hasText(array $pair): bool
    {
        return trim($pair['en'] ?? $pair['bn'] ?? '') !== '';
    }
}
