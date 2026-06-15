<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Collection;

class HeaderMenuSearch
{
    /**
     * Flat menu index for the header command palette.
     *
     * @return Collection<int, array{label: string, path: string, group: string}>
     */
    public static function itemsForUser(?User $user): Collection
    {
        if ($user === null) {
            return collect();
        }

        $menuGroups = collect(MenuHelper::getMenuGroups())
            ->map(function (array $group) use ($user) {
                $items = collect($group['items'] ?? [])
                    ->map(function (array $item) use ($user) {
                        $itemPermission = $item['permission'] ?? null;
                        $canSeeItem = empty($itemPermission) || Permission::can($user, $itemPermission);

                        if (! $canSeeItem) {
                            return null;
                        }

                        if (! MenuHelper::isValidMenuPath($item['path'] ?? null)) {
                            return null;
                        }

                        if (isset($item['subItems']) && is_array($item['subItems'])) {
                            $item['subItems'] = collect($item['subItems'])
                                ->filter(function (array $subItem) use ($user) {
                                    $permission = $subItem['permission'] ?? null;
                                    $hasPermission = empty($permission) || Permission::can($user, $permission);

                                    if (! $hasPermission) {
                                        return false;
                                    }

                                    return MenuHelper::isValidMenuPath($subItem['path'] ?? null);
                                })
                                ->values()
                                ->all();
                        }

                        return $item;
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'title' => $group['title'] ?? '',
                    'items' => $items,
                ];
            })
            ->filter(fn (array $group) => ! empty($group['items']))
            ->values();

        return $menuGroups
            ->flatMap(function (array $group) {
                return collect($group['items'])->flatMap(function (array $item) use ($group) {
                    $entries = [];

                    if (! empty($item['path']) && $item['path'] !== '#') {
                        $entries[] = [
                            'label' => $item['name'],
                            'path' => $item['path'],
                            'group' => $group['title'],
                        ];
                    }

                    foreach ($item['subItems'] ?? [] as $sub) {
                        if (! empty($sub['path']) && $sub['path'] !== '#') {
                            $entries[] = [
                                'label' => $sub['name'],
                                'path' => $sub['path'],
                                'group' => $group['title'],
                            ];
                        }
                    }

                    return $entries;
                });
            })
            ->values();
    }
}
