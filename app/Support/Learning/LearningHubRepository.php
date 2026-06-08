<?php

namespace App\Support\Learning;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class LearningHubRepository
{
    public static function ui(): array
    {
        return require resource_path('learning/ui.php');
    }

    /**
     * ERP user roles for the "I work in" filter (everyone + all roles except super_admin).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function roles(): array
    {
        $ui = require resource_path('learning/ui.php');
        $everyone = $ui['role_everyone'] ?? ['en' => 'Everyone', 'bn' => 'সবাই'];

        $roles = [
            ['slug' => 'all', 'label' => $everyone],
        ];

        if (! Schema::hasTable('roles')) {
            return require resource_path('learning/roles.php');
        }

        Role::query()
            ->where('key', '!=', 'super_admin')
            ->orderBy('label')
            ->get()
            ->each(function (Role $role) use (&$roles) {
                $roles[] = [
                    'slug' => $role->key,
                    'label' => [
                        'en' => $role->label,
                        'bn' => self::roleLabelBn($role->key, $role->label),
                    ],
                ];
            });

        return $roles;
    }

    protected static function roleLabelBn(string $key, string $fallback): string
    {
        $translated = trans('app.roles.' . $key, [], 'bn');

        return $translated !== 'app.roles.' . $key ? $translated : $fallback;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function modules(): array
    {
        $modules = require resource_path('learning/modules.php');
        $meta = require resource_path('learning/module-meta.php');

        return collect($modules)
            ->map(function (array $module) use ($meta) {
                $slug = $module['slug'] ?? '';
                $deepPath = resource_path('learning/deep/' . $slug . '.php');

                if (file_exists($deepPath)) {
                    $deep = require $deepPath;
                    if (! empty($deep['steps_extra'])) {
                        $module['steps'] = array_merge($module['steps'] ?? [], $deep['steps_extra']);
                        unset($deep['steps_extra']);
                    }
                    if (! empty($deep['examples_extra'])) {
                        $module['examples'] = array_merge($module['examples'] ?? [], $deep['examples_extra']);
                        unset($deep['examples_extra']);
                    }
                    if (! empty($deep['tips_extra'])) {
                        $module['tips'] = array_merge($module['tips'] ?? [], $deep['tips_extra']);
                        unset($deep['tips_extra']);
                    }
                    $module = array_merge($module, $deep);
                }

                $moduleMeta = $meta[$slug] ?? [];
                $module['roles'] = $module['roles'] ?? $moduleMeta['roles'] ?? ['all'];
                $module['related_screens'] = $module['related_screens'] ?? $moduleMeta['related_screens'] ?? [];

                return LearningCourseBuilder::enrichModule($module);
            })
            ->sortBy('order')
            ->values()
            ->all();
    }

    public static function module(string $slug): ?array
    {
        return collect(self::modules())->firstWhere('slug', $slug);
    }

    public static function defaultRoleFilter(?string $userRole): string
    {
        if ($userRole === null || $userRole === '' || $userRole === 'super_admin') {
            return 'all';
        }

        $slugs = collect(self::roles())->pluck('slug')->all();

        return in_array($userRole, $slugs, true) ? $userRole : 'all';
    }

    /**
     * @return array<int, string>
     */
    public static function learningPath(?string $role): array
    {
        return LearningCourseBuilder::pathForRole($role === null || $role === '' || $role === 'super_admin' ? 'all' : $role);
    }
}
