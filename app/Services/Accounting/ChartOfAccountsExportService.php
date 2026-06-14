<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Support\ExportDateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ChartOfAccountsExportService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function pickerRows(): array
    {
        return Account::query()
            ->with('parent')
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'parent' => $account->parent?->name ?? '—',
                'kind' => $account->is_group ? 'Group' : 'Ledger',
                'slug' => $account->slug ?? '—',
                'type' => $account->type,
                'level' => $account->level,
                'is_group' => $account->is_group,
            ])
            ->all();
    }

    public function rows(Request $request): Collection
    {
        return $this->structureRows($request);
    }

    public function structureRows(Request $request): Collection
    {
        return $this->filteredAccounts($request)->map(fn (Account $account) => [
            $account->code,
            $account->name,
            $account->parent?->name ?? '—',
            $account->is_group ? 'Group' : 'Ledger',
            $account->slug ?? '—',
            ucfirst($account->type ?? ''),
            $account->is_active ? 'Active' : 'Inactive',
            (string) $account->level,
        ]);
    }

    public function balanceRows(Request $request): Collection
    {
        [$from, $to] = $this->resolveBalancePeriod($request);
        $allAccounts = Account::query()->orderBy('code')->get();
        $summaryService = app(ChartOfAccountsSummaryService::class);
        $summary = $summaryService->summarize($from, $to, $allAccounts);

        return $this->filteredAccounts($request)->map(function (Account $account) use ($summaryService, $summary) {
            $balance = $summaryService->rollupBalance(
                $account,
                $summary['period'],
                $summary['closing']
            );
            $balanceMode = in_array($account->type, ['income', 'expense'], true) ? 'Period total' : 'Closing';

            return [
                $account->code,
                $account->name,
                $account->parent?->name ?? '—',
                $account->is_group ? 'Group' : 'Ledger',
                ucfirst($account->type ?? ''),
                number_format($balance, 2, '.', ''),
                $balanceMode,
            ];
        });
    }

    /**
     * @return Collection<int, Account>
     */
    protected function filteredAccounts(Request $request): Collection
    {
        $selectedType = $this->normalizeType($request->query('type'));
        $ledgersOnly = $request->boolean('ledgers_only');
        $selectedIds = $this->selectedAccountIds($request);
        $selectedRoot = $this->normalizeReportRoot($request->query('root'));

        return $this->baseQuery($request)
            ->when($selectedType, fn (Builder $q) => $q->where('type', $selectedType))
            ->when($ledgersOnly, fn (Builder $q) => $q->where('is_group', false))
            ->when($selectedIds !== [], fn (Builder $q) => $q->whereIn('id', $selectedIds))
            ->when($selectedRoot, fn (Builder $q) => $q->where('report_root', $selectedRoot))
            ->orderBy('code')
            ->get();
    }

    protected function baseQuery(Request $request): Builder
    {
        return Account::query()->with('parent');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveBalancePeriod(Request $request): array
    {
        $today = Carbon::today();
        $resolved = ExportDateRange::resolve($request);

        if ($resolved && $resolved['from'] && $resolved['to']) {
            return [$resolved['from'], $resolved['to']];
        }

        $range = $request->query('range');
        $hasCustomDates = $request->filled('from') || $request->filled('to');

        if ($range === 'custom' || ($range === null && $hasCustomDates) || $hasCustomDates) {
            $from = $request->filled('from')
                ? Carbon::parse($request->query('from'))->startOfDay()
                : $today->copy()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->query('to'))->endOfDay()
                : $today->copy()->endOfMonth();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to];
        }

        if ($range === 'all') {
            return [$today->copy()->startOfMonth(), $today->copy()->endOfDay()];
        }

        [$from, $to] = match ($range) {
            '7d' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            'quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfDay()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfDay()],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfDay()],
        };

        return [$from, $to];
    }

    protected function normalizeReportRoot(?string $root): ?string
    {
        return in_array($root, Account::REPORT_ROOTS, true) ? $root : null;
    }

    /**
     * @return array<int, string>
     */
    public function normalizeType(?string $type): ?string
    {
        $validTypes = ['asset', 'liability', 'equity', 'income', 'expense'];

        return in_array($type, $validTypes, true) ? $type : null;
    }

    /**
     * @return array<int, int>
     */
    protected function selectedAccountIds(Request $request): array
    {
        $ids = $request->input('ledger_ids', $request->input('accounts', []));

        if (! is_array($ids)) {
            $ids = array_filter(explode(',', (string) $ids));
        }

        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Account>  $nodes
     * @return \Illuminate\Support\Collection<int, Account>
     */
    public function pruneTreeByType(Collection $nodes, ?string $type): Collection
    {
        if (! $type) {
            return $nodes;
        }

        return $nodes
            ->map(function (Account $node) use ($type) {
                $children = $this->pruneTreeByType($node->children, $type);
                $node->setRelation('children', $children);

                if ($node->type === $type || $children->isNotEmpty()) {
                    return $node;
                }

                return null;
            })
            ->filter()
            ->values();
    }
}
