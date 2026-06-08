<?php

namespace App\Support\Learning;

class LearningCourseBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function lessons(array $module): array
    {
        $lessons = [];

        $lessons[] = [
            'id' => 'welcome',
            'type' => 'welcome',
            'title' => [
                'en' => 'Course overview',
                'bn' => 'কোর্স পরিচিতি',
            ],
            'duration_min' => 3,
        ];

        if (! empty($module['flowchart'])) {
            $lessons[] = [
                'id' => 'flow',
                'type' => 'flow',
                'title' => [
                    'en' => 'Process flow',
                    'bn' => 'প্রক্রিয়ার ধাপ',
                ],
                'duration_min' => 4,
            ];
        }

        foreach ($module['steps'] ?? [] as $index => $step) {
            $lessons[] = [
                'id' => 'step-' . $index,
                'type' => 'step',
                'title' => $step['title'] ?? ['en' => 'Step', 'bn' => 'ধাপ'],
                'body' => $step['body'] ?? ['en' => '', 'bn' => ''],
                'path' => $step['path'] ?? null,
                'step_number' => $index + 1,
                'duration_min' => 5,
            ];
        }

        if (! empty($module['examples']) || ! empty($module['tips']) || ! empty($module['quick_start'])) {
            $lessons[] = [
                'id' => 'practice',
                'type' => 'practice',
                'title' => [
                    'en' => 'Real examples & tips',
                    'bn' => 'বাস্তব উদাহরণ ও টিপস',
                ],
                'duration_min' => 5,
            ];
        }

        if (self::hasReference($module)) {
            $lessons[] = [
                'id' => 'reference',
                'type' => 'reference',
                'title' => [
                    'en' => 'Expert reference',
                    'bn' => 'বিশেষজ্ঞ রেফারেন্স',
                ],
                'duration_min' => 6,
            ];
        }

        $glossaryTerms = LearningGlossary::terms($module);
        if (count($glossaryTerms) > 0) {
            $lessons[] = [
                'id' => 'glossary',
                'type' => 'glossary',
                'title' => [
                    'en' => 'Glossary — key terms',
                    'bn' => 'শব্দকোষ — গুরুত্বপূর্ণ শব্দ',
                ],
                'duration_min' => 4,
                'terms' => $glossaryTerms,
            ];
        }

        $lessons[] = [
            'id' => 'quiz',
            'type' => 'quiz',
            'title' => [
                'en' => 'Knowledge check',
                'bn' => 'জ্ঞান যাচাই',
            ],
            'duration_min' => 5,
        ];

        return LearningEnrichment::applyToLessons($module, $lessons);
    }

    public static function hasReference(array $module): bool
    {
        return ! empty($module['deep_sections'])
            || ! empty($module['faqs'])
            || ! empty($module['report_links']);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function quiz(string $slug): ?array
    {
        $all = require resource_path('learning/quizzes.php');

        return $all[$slug] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function courseMeta(string $slug): array
    {
        $all = require resource_path('learning/course-meta.php');
        $defaults = [
            'track' => ['en' => 'General', 'bn' => 'সাধারণ'],
            'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
            'duration_min' => 10,
            'outcomes' => [],
        ];

        return array_merge($defaults, $all[$slug] ?? []);
    }

    /**
     * @return array<int, string>
     */
    public static function pathForRole(string $role): array
    {
        $paths = require resource_path('learning/paths.php');

        return $paths[$role] ?? $paths['all'] ?? [];
    }

    public static function enrichModule(array $module): array
    {
        $slug = $module['slug'] ?? '';
        $lessons = self::lessons($module);
        $meta = self::courseMeta($slug);
        $quiz = self::quiz($slug);

        $module['course'] = [
            'meta' => $meta,
            'lessons' => $lessons,
            'lesson_count' => count($lessons),
            'duration_min' => (int) collect($lessons)->sum('duration_min'),
            'quiz_question_count' => count($quiz['questions'] ?? []),
            'pass_percent' => (int) ($quiz['pass_percent'] ?? 70),
        ];

        if ($quiz !== null) {
            $module['quiz'] = $quiz;
        }

        return $module;
    }
}
