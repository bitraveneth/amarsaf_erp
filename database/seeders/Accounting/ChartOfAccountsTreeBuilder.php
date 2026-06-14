<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use App\Services\Accounting\AccountPathService;
use App\Services\Accounting\AccountResolver;

class ChartOfAccountsTreeBuilder
{
    protected int $sort = 0;

    public function __construct(
        protected AccountPathService $paths,
        protected AccountResolver $resolver
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public function seed(array $nodes, ?Account $parent = null): void
    {
        foreach ($nodes as $node) {
            $this->seedNode($node, $parent);
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected function seedNode(array $node, ?Account $parent): Account
    {
        $slug = $node['slug'] ?? null;
        $isGroup = (bool) ($node['is_group'] ?? ! empty($node['children']));

        $attributes = [
            'name' => $node['name'],
            'type' => $node['type'],
            'is_group' => $isGroup,
            'level' => $parent ? $parent->level + 1 : 0,
            'sort_order' => ++$this->sort,
            'report_root' => $node['report_root'] ?? $parent?->report_root,
            'is_active' => $node['is_active'] ?? true,
            'parent_id' => $parent?->id,
            'code' => $node['code'],
        ];

        if ($slug) {
            $attributes['slug'] = $slug;
            $account = Account::query()->where('slug', $slug)->first();

            if (! $account) {
                $account = Account::query()->where('code', $node['code'])->whereNull('slug')->first();
            }

            if ($account) {
                $account->update($attributes);
            } else {
                $account = Account::create($attributes);
            }
        } else {
            $account = Account::updateOrCreate([
                'code' => $node['code'],
                'parent_id' => $parent?->id,
            ], $attributes);
        }

        $this->paths->refresh($account);

        foreach ($node['children'] ?? [] as $child) {
            $this->seedNode($child, $account);
        }

        return $account;
    }

    public function clearCache(): void
    {
        $this->resolver->forget();
    }
}
