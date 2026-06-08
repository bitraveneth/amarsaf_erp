<?php

namespace App\Support\Learning;

class LearningGlossary
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function terms(array $module): array
    {
        $slug = $module['slug'] ?? '';
        $catalog = require resource_path('learning/glossaries.php');
        $merged = [];

        foreach ($catalog[$slug] ?? [] as $entry) {
            $merged[self::termKey($entry)] = $entry;
        }

        foreach ($module['glossary'] ?? [] as $entry) {
            $merged[self::termKey($entry)] = $entry;
        }

        foreach ($catalog['_common'] ?? [] as $entry) {
            $tags = $entry['courses'] ?? ['all'];
            if (in_array('all', $tags, true) || in_array($slug, $tags, true)) {
                $key = self::termKey($entry);
                if (! isset($merged[$key])) {
                    $merged[$key] = $entry;
                }
            }
        }

        return array_values($merged);
    }

    protected static function termKey(array $entry): string
    {
        $term = $entry['term']['en'] ?? $entry['term']['bn'] ?? '';

        return strtolower(trim($term));
    }
}
