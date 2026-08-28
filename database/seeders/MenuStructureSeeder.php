<?php

namespace Database\Seeders;

use App\Helpers\MenuHelper;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $structure = MenuHelper::getFallbackMenu();
        $activeGroupKeys = [];
        $activeItemIds = [];

        foreach ($structure as $groupIndex => $group) {
            $groupKey = $group['key'] ?? Str::slug($group['title']);
            if ($groupKey === '') {
                $groupKey = 'dashboard';
            }

            $activeGroupKeys[] = $groupKey;

            $menuGroup = MenuGroup::updateOrCreate(
                ['key' => $groupKey],
                [
                    'title'    => $group['title'],
                    'position' => $groupIndex,
                    'is_active'=> true,
                ]
            );

            foreach ($group['items'] as $itemIndex => $item) {
                $menuItem = MenuItem::updateOrCreate(
                    [
                        'menu_group_id' => $menuGroup->id,
                        'key'           => $item['name'],
                        'parent_id'     => null,
                    ],
                    [
                        'name'       => $item['name'],
                        'icon'       => $item['icon'] ?? null,
                        'path'       => $item['path'] ?? '#',
                        'permission' => $item['permission'] ?? null,
                        'position'   => $itemIndex,
                        'is_active'  => true,
                    ]
                );

                $activeItemIds[] = $menuItem->id;

                if (!empty($item['subItems'])) {
                    foreach ($item['subItems'] as $subIndex => $subItem) {
                        $childItem = MenuItem::updateOrCreate(
                            [
                                'menu_group_id' => $menuGroup->id,
                                'parent_id'     => $menuItem->id,
                                'name'          => $subItem['name'],
                            ],
                            [
                                'icon'       => null,
                                'path'       => $subItem['path'] ?? '#',
                                'permission' => $subItem['permission'] ?? null,
                                'position'   => $subIndex,
                                'is_active'  => true,
                            ]
                        );

                        $activeItemIds[] = $childItem->id;
                    }
                }
            }
        }

        // Cleanup deprecated duplicate link:
        // "Supplier prices" previously pointed to the same path as "Suppliers".
        MenuItem::where('name', 'Supplier prices')
            ->where('path', '/admin/suppliers')
            ->delete();

        // Cleanup misplaced warehouse submenu.
        MenuItem::where('name', 'Expenses & allowances')
            ->where('path', '/admin/expenses')
            ->delete();

        MenuItem::query()
            ->where('name', 'Books for finance managers')
            ->where('path', 'like', '/admin/client-guide%')
            ->delete();

        // Vehicle loads belongs under Logistics only (not Delivery).
        $deliveryParentId = MenuItem::query()
            ->whereNull('parent_id')
            ->where('name', 'Delivery')
            ->value('id');

        if ($deliveryParentId) {
            MenuItem::query()
                ->where('parent_id', $deliveryParentId)
                ->where('name', 'Vehicle loads')
                ->where('path', '/admin/vehicle-load')
                ->delete();
        }

        MenuGroup::query()
            ->whereIn('key', ['overview'])
            ->orWhereRaw('LOWER(TRIM(title)) = ?', ['overview'])
            ->update(['title' => '', 'key' => 'dashboard']);

        MenuGroup::query()
            ->whereNotIn('key', $activeGroupKeys)
            ->update(['is_active' => false]);

        $activeGroupIds = MenuGroup::query()
            ->whereIn('key', $activeGroupKeys)
            ->pluck('id');

        MenuItem::query()
            ->whereIn('menu_group_id', $activeGroupIds)
            ->whereNotIn('id', $activeItemIds)
            ->update(['is_active' => false]);

        // Deactivate deprecated top-level menu entries only (not submenu links like Products under Products & catalog).
        MenuItem::query()
            ->whereNull('parent_id')
            ->whereIn('name', [
                'Warehouses & logistics setup',
                'Fulfillment & delivery',
                'Employees',
                'Products',
                'Sales',
                'GRN inbox',
            ])
            ->update(['is_active' => false]);

        $this->syncMenuLabelsFromFallback();
    }

    protected function syncMenuLabelsFromFallback(): void
    {
        $labelsByPath = [];

        foreach (MenuHelper::getFallbackMenu() as $group) {
            foreach ($group['items'] as $item) {
                if (! empty($item['path']) && ($item['path'] ?? '#') !== '#') {
                    $labelsByPath[$item['path']] = $item['name'];
                }

                foreach ($item['subItems'] ?? [] as $subItem) {
                    if (! empty($subItem['path'])) {
                        $labelsByPath[$subItem['path']] = $subItem['name'];
                    }
                }
            }
        }

        foreach ($labelsByPath as $path => $name) {
            MenuItem::where('path', $path)->update(['name' => $name]);
        }
    }
}
