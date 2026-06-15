<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IncomeStatementPresenter
{
    /** @var array<int, string> */
    protected array $defaultExpandedGroups = [];

    /** @var array<int, string> */
    protected array $allGroupCodes = [
        '4100', '4200',
        '5100', '5200', '5300', '5400',
        '6100', '6200', '6300',
    ];

    public function __construct(protected PlDriverLinkService $driverLinks)
    {
    }

    /**
     * @param  array<string, mixed>  $statement
     * @param  array<string, mixed>|null  $priorStatement
     * @return array<string, mixed>
     */
    public function present(
        array $statement,
        string $periodLabel,
        string $currencyCode,
        Carbon $from,
        Carbon $to,
        ?array $priorStatement = null,
        ?string $priorPeriodLabel = null
    ): array {
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $priorNetRevenue = $priorStatement ? (float) ($priorStatement['netRevenue'] ?? 0) : 0;

        $journalCounts = $this->journalCountsByAccount($from, $to);
        $groupCodes = [];
        $rows = $this->buildRows($statement, $groupCodes, $netRevenue, $from, $to, $journalCounts);

        if ($priorStatement) {
            $priorGroupCodes = [];
            $priorRows = collect($this->buildRows($priorStatement, $priorGroupCodes, $priorNetRevenue, $from, $to, collect()))
                ->keyBy(fn (array $row) => $this->rowKey($row));

            $rows = collect($rows)->map(function (array $row) use ($priorRows, $priorNetRevenue) {
                $key = $this->rowKey($row);
                $prior = $priorRows->get($key);
                $currentAmount = (float) ($row['amount'] ?? 0);
                $priorAmount = $prior ? (float) ($prior['amount'] ?? 0) : 0.0;
                $variance = round($currentAmount - $priorAmount, 2);
                $variancePct = abs($priorAmount) >= 0.01
                    ? round(($variance / abs($priorAmount)) * 100, 1)
                    : null;

                $row['prior_display'] = $prior
                    ? ($prior['amount_display'] ?? $this->formatAmount($priorAmount, ! empty($row['is_cost'])))
                    : '—';
                $row['variance_display'] = $prior ? $this->formatVariance($variance, ! empty($row['is_cost'])) : '—';
                $row['variance_pct_display'] = $variancePct !== null
                    ? ($variancePct >= 0 ? '+' : '') . $variancePct . '%'
                    : '—';
                $row['variance_tone'] = $variance > 0 ? 'up' : ($variance < 0 ? 'down' : 'flat');

                return $row;
            })->all();
        }

        return [
            'periodLabel' => $periodLabel,
            'priorPeriodLabel' => $priorPeriodLabel,
            'currencyCode' => $currencyCode,
            'groupCodes' => $groupCodes,
            'defaultExpandedGroups' => $groupCodes ?: $this->allGroupCodes,
            'rows' => $rows,
            'blocks' => $this->structureBlocks($rows),
            'summary' => $this->summary($statement, $currencyCode),
            'calculation' => $this->calculationGuide($statement, $currencyCode),
            'trust' => $this->trustMeta($from, $to),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'hasComparison' => $priorStatement !== null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function structureBlocks(array $rows): array
    {
        $blocks = [];
        $currentSection = null;

        foreach ($rows as $row) {
            $type = $row['type'] ?? '';

            if ($type === 'section') {
                $currentSection = [
                    'kind' => 'section',
                    'meta' => $row,
                    'items' => [],
                ];
                $blocks[] = $currentSection;
                $currentSection = &$blocks[array_key_last($blocks)];

                continue;
            }

            if ($type === 'group' && $currentSection !== null) {
                $currentSection['items'][] = [
                    'kind' => 'group',
                    'meta' => $row,
                    'accounts' => [],
                ];

                continue;
            }

            if ($type === 'account' && $currentSection !== null) {
                $lastIndex = array_key_last($currentSection['items']);
                if ($lastIndex !== null && ($currentSection['items'][$lastIndex]['kind'] ?? '') === 'group') {
                    $currentSection['items'][$lastIndex]['accounts'][] = $row;
                }

                continue;
            }

            if ($type === 'milestone') {
                if ($currentSection !== null && ($row['milestone_key'] ?? '') === 'operating') {
                    $currentSection['items'][] = [
                        'kind' => 'milestone',
                        'meta' => $row,
                    ];
                } else {
                    $currentSection = null;
                    $blocks[] = [
                        'kind' => 'milestone',
                        'meta' => $row,
                    ];
                }
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $statement
     * @return array<string, mixed>
     */
    protected function summary(array $statement, string $currencyCode): array
    {
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $netProfit = (float) ($statement['netProfit'] ?? 0);
        $grossProfit = (float) ($statement['grossProfit'] ?? 0);

        return [
            'currency' => $currencyCode,
            'net_revenue' => $this->formatPlain($netRevenue),
            'gross_profit' => $this->formatPlain($grossProfit),
            'net_profit' => $this->formatPlain($netProfit),
            'gross_margin' => $netRevenue > 0 ? round($grossProfit / $netRevenue * 100, 1) : 0,
            'net_margin' => $netRevenue > 0 ? round($netProfit / $netRevenue * 100, 1) : 0,
            'is_profit' => $netProfit >= 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @return array<string, mixed>
     */
    protected function calculationGuide(array $statement, string $currencyCode): array
    {
        $grossSales = (float) ($statement['grossSales'] ?? 0);
        $salesReturns = abs((float) ($statement['salesReturns'] ?? 0));
        $otherIncome = (float) ($statement['otherIncome'] ?? 0);
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $cogs = abs((float) ($statement['manufacturingCost'] ?? 0));
        $grossProfit = (float) ($statement['grossProfit'] ?? 0);
        $admin = abs((float) ($statement['administrativeExpenses'] ?? 0));
        $selling = abs((float) ($statement['sellingExpenses'] ?? 0));
        $operatingProfit = (float) ($statement['operatingProfit'] ?? 0);
        $financial = abs((float) ($statement['financialExpenses'] ?? 0));
        $netProfit = (float) ($statement['netProfit'] ?? 0);

        $f = fn (float $n): string => $this->formatPlain($n);
        $fc = fn (float $n): string => $this->formatPlain(abs($n));

        return [
            'currency' => $currencyCode,
            'source' => 'Posted journal entries in the general ledger for the selected period.',
            'steps' => [
                [
                    'label' => 'Net revenue',
                    'formula' => 'Gross sales − Sales returns + Other income',
                    'expression' => $f($grossSales) . ' − ' . $f($salesReturns) . ' + ' . $f($otherIncome) . ' = ' . $f($netRevenue),
                    'result' => $f($netRevenue),
                ],
                [
                    'label' => 'Gross profit',
                    'formula' => 'Net revenue − Cost of goods sold',
                    'expression' => $f($netRevenue) . ' − ' . $fc($cogs) . ' = ' . $f($grossProfit),
                    'result' => $f($grossProfit),
                ],
                [
                    'label' => 'Operating profit',
                    'formula' => 'Gross profit − Administrative − Selling & distribution',
                    'expression' => $f($grossProfit) . ' − ' . $fc($admin) . ' − ' . $fc($selling) . ' = ' . $f($operatingProfit),
                    'result' => $f($operatingProfit),
                ],
                [
                    'label' => 'Net profit',
                    'formula' => 'Operating profit − Financial expenses',
                    'expression' => $f($operatingProfit) . ' − ' . $fc($financial) . ' = ' . $f($netProfit),
                    'result' => $f($netProfit),
                ],
            ],
            'margins' => [
                [
                    'label' => 'Gross margin',
                    'formula' => 'Gross profit ÷ Net revenue',
                    'expression' => $f($grossProfit) . ' ÷ ' . $f($netRevenue) . ' = ' . ($netRevenue > 0 ? round($grossProfit / $netRevenue * 100, 1) : 0) . '%',
                ],
                [
                    'label' => 'Net margin',
                    'formula' => 'Net profit ÷ Net revenue',
                    'expression' => $f($netProfit) . ' ÷ ' . $f($netRevenue) . ' = ' . ($netRevenue > 0 ? round($netProfit / $netRevenue * 100, 1) : 0) . '%',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function trustMeta(Carbon $from, Carbon $to): array
    {
        $lastJournal = \App\Models\JournalEntry::query()
            ->where('status', 'posted')
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first();

        return [
            'last_journal_at' => $lastJournal?->entry_date?->format('d M Y'),
            'last_journal_ref' => $lastJournal?->number,
        ];
    }

    protected function formatPlain(float $amount): string
    {
        if ($amount < 0) {
            return '(' . number_format(abs($amount), 2) . ')';
        }

        return number_format($amount, 2);
    }

    /**
     * @param  array<int, string>  $groupCodes
     * @return array<int, array<string, mixed>>
     */
    protected function buildRows(
        array $statement,
        array &$groupCodes,
        float $netRevenue,
        Carbon $from,
        Carbon $to,
        Collection $journalCounts
    ): array {
        $rows = [];

        $rows = array_merge($rows, $this->buildSectionRows(
            $groupCodes,
            $netRevenue,
            $from,
            $to,
            $journalCounts,
            'revenue',
            'Total income',
            $statement['revenue']['rows'] ?? collect(),
            [
                ['code' => '4100', 'label' => 'Sales & services'],
                ['code' => '4200', 'label' => 'Other income'],
            ],
            (float) ($statement['netRevenue'] ?? 0),
            false
        ));

        $rows = array_merge($rows, $this->buildSectionRows(
            $groupCodes,
            $netRevenue,
            $from,
            $to,
            $journalCounts,
            'cogs',
            'Cost of goods sold',
            $statement['manufacturing']['rows'] ?? collect(),
            [
                ['code' => '5100', 'label' => 'Direct material cost'],
                ['code' => '5200', 'label' => 'Direct labor cost'],
                ['code' => '5300', 'label' => 'Manufacturing overhead'],
                ['code' => '5400', 'label' => 'Inventory adjustment'],
            ],
            (float) ($statement['manufacturingCost'] ?? 0),
            true
        ));

        $rows[] = $this->milestoneRow(
            'Gross profit',
            (float) ($statement['grossProfit'] ?? 0),
            $netRevenue,
            'gross'
        );

        $operatingRows = $statement['operating']['rows'] ?? collect();

        $rows[] = $this->sectionRow(
            'opex',
            'Running costs (OpEx)',
            (float) ($statement['operatingExpenses'] ?? 0),
            $netRevenue,
            true
        );

        foreach (
            [
                ['code' => '6100', 'label' => 'Staff, office & admin'],
                ['code' => '6200', 'label' => 'Sales, marketing & logistics'],
            ] as $groupConfig
        ) {
            $rows = array_merge(
                $rows,
                $this->buildGroupRows(
                    $groupCodes,
                    $netRevenue,
                    $from,
                    $to,
                    $journalCounts,
                    'opex',
                    $operatingRows,
                    $groupConfig,
                    true
                )
            );
        }

        $rows[] = $this->milestoneRow(
            'Operating profit',
            (float) ($statement['operatingProfit'] ?? 0),
            $netRevenue,
            'operating'
        );

        $rows = array_merge(
            $rows,
            $this->buildGroupRows(
                $groupCodes,
                $netRevenue,
                $from,
                $to,
                $journalCounts,
                'opex',
                $operatingRows,
                ['code' => '6300', 'label' => 'Finance & bank charges'],
                true
            )
        );

        $netProfit = (float) ($statement['netProfit'] ?? 0);
        $rows[] = $this->milestoneRow(
            'Net profit' . ($netProfit < 0 ? ' (Loss)' : ''),
            $netProfit,
            $netRevenue,
            'net'
        );

        return $rows;
    }

    protected function rowKey(array $row): string
    {
        return match ($row['type'] ?? '') {
            'section' => 'section:' . ($row['section_key'] ?? ''),
            'group' => 'group:' . ($row['group_code'] ?? ''),
            'milestone' => 'milestone:' . ($row['milestone_key'] ?? ''),
            'account' => 'account:' . ($row['group_code'] ?? '') . ':' . ($row['code'] ?? ''),
            default => 'row:' . ($row['label'] ?? ''),
        };
    }

    /**
     * @param  array<int, string>  $groupCodes
     * @param  array<int, array{code: string, label: string}>  $groups
     * @return array<int, array<string, mixed>>
     */
    protected function buildSectionRows(
        array &$groupCodes,
        float $netRevenue,
        Carbon $from,
        Carbon $to,
        Collection $journalCounts,
        string $sectionKey,
        string $title,
        mixed $sourceRows,
        array $groups,
        float $sectionTotal,
        bool $isCost
    ): array {
        $rows = [$this->sectionRow($sectionKey, $title, $sectionTotal, $netRevenue, $isCost)];

        foreach ($groups as $groupConfig) {
            $rows = array_merge(
                $rows,
                $this->buildGroupRows(
                    $groupCodes,
                    $netRevenue,
                    $from,
                    $to,
                    $journalCounts,
                    $sectionKey,
                    $sourceRows,
                    $groupConfig,
                    $isCost
                )
            );
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $groupCodes
     * @param  array{code: string, label: string}  $groupConfig
     * @return array<int, array<string, mixed>>
     */
    protected function buildGroupRows(
        array &$groupCodes,
        float $netRevenue,
        Carbon $from,
        Carbon $to,
        Collection $journalCounts,
        string $sectionKey,
        mixed $sourceRows,
        array $groupConfig,
        bool $isCost
    ): array {
        $code = $groupConfig['code'];
        $total = $this->groupSubtotal($sourceRows, $code);
        $lines = $this->allGroupDetailLines($sourceRows, $code);

        $groupCodes[] = $code;
        $firstLine = $lines->first(fn (array $line) => empty($line['is_subtotal']));
        $baseLevel = $firstLine ? (int) ($firstLine['level'] ?? 1) : ((int) (Account::query()->where('code', $code)->value('level') ?? 1) + 1);
        $displayTotal = $isCost ? abs($total) : $total;

        $rows = [[
            'type' => 'group',
            'section_key' => $sectionKey,
            'group_code' => $code,
            'code' => $code,
            'label' => $groupConfig['label'],
            'amount' => $isCost ? abs($total) : $total,
            'amount_display' => $this->formatAmount($displayTotal, $isCost),
            'pct_display' => $this->pctOfRevenue($displayTotal, $netRevenue),
            'indent' => 1,
            'is_cost' => $isCost,
            'expandable' => $lines->isNotEmpty(),
        ]];

        foreach ($lines as $line) {
            $rows[] = $this->accountRow($line, $baseLevel, $code, $sectionKey, $netRevenue, $isCost, $from, $to, $journalCounts);
        }

        return $rows;
    }

    /**
     * Full chart-of-accounts detail for a statement group — every ledger line is listed,
     * including accounts with no activity in the period (shown as 0.00).
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function allGroupDetailLines(mixed $sourceRows, string $groupCode): Collection
    {
        $activity = collect($sourceRows)->keyBy(fn (array $row) => (string) ($row['code'] ?? ''));

        $nodes = Account::query()
            ->where('code', 'like', $groupCode . '%')
            ->orderBy('code')
            ->get();

        if ($nodes->isEmpty()) {
            return $this->groupLines($sourceRows, $groupCode);
        }

        $root = $nodes->first(fn (Account $account) => $account->code === $groupCode);
        if (! $root) {
            return $this->groupLines($sourceRows, $groupCode);
        }

        $childrenByParent = $nodes->groupBy('parent_id');
        $lines = collect();
        $this->walkCatalogGroup($lines, $root, $childrenByParent, $activity, true);

        return $lines->values();
    }

    /**
     * @param  Collection<int|string, Collection<int, Account>>  $childrenByParent
     * @param  Collection<string, array<string, mixed>>  $activity
     */
    protected function walkCatalogGroup(
        Collection $lines,
        Account $node,
        Collection $childrenByParent,
        Collection $activity,
        bool $isRoot
    ): void {
        $children = $childrenByParent->get($node->id, collect())->sortBy('code')->values();

        if ($node->is_group && ! $isRoot) {
            $lines->push($this->mergeCatalogRow($node, $this->sumCatalogLeaves($node, $childrenByParent, $activity), true));
        }

        if ($node->is_group) {
            foreach ($children as $child) {
                $this->walkCatalogGroup($lines, $child, $childrenByParent, $activity, false);
            }

            return;
        }

        $lines->push($this->mergeCatalogRow($node, (float) ($activity->get($node->code)['amount'] ?? 0), false));
    }

    /**
     * @param  Collection<int|string, Collection<int, Account>>  $childrenByParent
     * @param  Collection<string, array<string, mixed>>  $activity
     */
    protected function sumCatalogLeaves(Account $node, Collection $childrenByParent, Collection $activity): float
    {
        $total = 0.0;
        $stack = [$node->id];

        while ($stack !== []) {
            $parentId = array_pop($stack);
            foreach ($childrenByParent->get($parentId, collect()) as $child) {
                if ($child->is_group) {
                    $stack[] = $child->id;
                } else {
                    $total += (float) ($activity->get($child->code)['amount'] ?? 0);
                }
            }
        }

        return $total;
    }

    /**
     * @return array<string, mixed>
     */
    protected function mergeCatalogRow(Account $node, float $amount, bool $isSubgroup): array
    {
        return [
            'code' => $node->code,
            'name' => $node->name,
            'slug' => $node->slug,
            'level' => $node->level,
            'amount' => $amount,
            'is_subtotal' => $isSubgroup,
            'is_group' => $node->is_group,
            'id' => $node->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sectionRow(
        string $sectionKey,
        string $title,
        float $amount,
        float $netRevenue,
        bool $isCost
    ): array {
        $displayAmount = $isCost ? abs($amount) : $amount;

        return [
            'type' => 'section',
            'section_key' => $sectionKey,
            'group_code' => null,
            'code' => null,
            'label' => $title,
            'amount' => $displayAmount,
            'amount_display' => $this->formatAmount($displayAmount, $isCost),
            'pct_display' => $this->pctOfRevenue($displayAmount, $netRevenue),
            'indent' => 0,
            'is_cost' => $isCost,
            'expandable' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function milestoneRow(string $label, float $amount, float $netRevenue, string $key): array
    {
        return [
            'type' => 'milestone',
            'milestone_key' => $key,
            'section_key' => null,
            'group_code' => null,
            'code' => null,
            'label' => $label,
            'amount' => $amount,
            'amount_display' => $this->formatAmount($amount),
            'pct_display' => $this->pctOfRevenue($amount, $netRevenue),
            'indent' => 0,
            'is_cost' => false,
            'tone' => $amount >= 0 ? 'positive' : 'negative',
            'expandable' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function accountRow(
        array $line,
        int $baseLevel,
        string $groupCode,
        string $sectionKey,
        float $netRevenue,
        bool $isCost,
        Carbon $from,
        Carbon $to,
        Collection $journalCounts
    ): array {
        $level = (int) ($line['level'] ?? 0);
        $amount = (float) ($line['amount'] ?? 0);
        $displayAmount = $isCost ? abs($amount) : $amount;
        $isSubgroup = ! empty($line['is_subtotal']);
        $isGroup = ! empty($line['is_group']);
        $accountId = $line['id'] ?? null;
        $slug = $line['slug'] ?? null;
        $indent = max(0, $level - $baseLevel) + 2;

        $glUrl = null;
        if ($accountId && ! $isGroup && ! $isSubgroup) {
            $glUrl = route('admin.reports.general-ledger', [
                'account_id' => $accountId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]);
        }

        $sourceLink = $this->driverLinks->accountLink($slug, $from, $to);
        $journalCount = $accountId ? (int) ($journalCounts->get($accountId, 0)) : 0;

        return [
            'type' => 'account',
            'section_key' => $sectionKey,
            'group_code' => $groupCode,
            'code' => $line['code'] ?? '',
            'label' => $line['name'] ?? '',
            'slug' => $slug,
            'amount' => $displayAmount,
            'amount_display' => $this->formatAmount($displayAmount, $isCost),
            'pct_display' => $this->pctOfRevenue($displayAmount, $netRevenue),
            'indent' => $indent,
            'is_cost' => $isCost,
            'is_subgroup' => $isSubgroup,
            'account_id' => $accountId,
            'gl_url' => $glUrl,
            'source_url' => $sourceLink['href'] ?? null,
            'source_title' => $sourceLink['title'] ?? null,
            'journal_count' => $journalCount,
            'expandable' => false,
        ];
    }

    protected function journalCountsByAccount(Carbon $from, Carbon $to): Collection
    {
        return JournalEntryLine::query()
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $from->toDateString())
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->selectRaw('account_id, COUNT(DISTINCT journal_entry_id) as entry_count')
            ->groupBy('account_id')
            ->pluck('entry_count', 'account_id');
    }

    protected function pctOfRevenue(float $amount, float $netRevenue): string
    {
        if (abs($netRevenue) < 0.01) {
            return '—';
        }

        return number_format(abs($amount) / abs($netRevenue) * 100, 1) . '%';
    }

    protected function formatAmount(float $amount, bool $isCost = false): string
    {
        if ($isCost && $amount > 0) {
            return '(' . number_format($amount, 2) . ')';
        }

        if (! $isCost && $amount < 0) {
            return '(' . number_format(abs($amount), 2) . ')';
        }

        return number_format($amount, 2);
    }

    protected function formatVariance(float $variance, bool $isCost): string
    {
        if (abs($variance) < 0.01) {
            return '0.00';
        }

        $prefix = $variance > 0 ? '+' : '';

        return $prefix . number_format($variance, 2);
    }

    protected function groupSubtotal(mixed $rows, string $code): float
    {
        $row = collect($rows)->first(
            fn (array $item) => ($item['code'] ?? '') === $code && ! empty($item['is_subtotal'])
        );

        return (float) ($row['amount'] ?? 0);
    }

    protected function groupLines(mixed $rows, string $code): Collection
    {
        return collect($rows)
            ->filter(function (array $row) use ($code) {
                $rowCode = (string) ($row['code'] ?? '');
                if ($rowCode === '' || ! str_starts_with($rowCode, $code)) {
                    return false;
                }
                if ($rowCode === $code && ! empty($row['is_subtotal'])) {
                    return false;
                }

                return abs((float) ($row['amount'] ?? 0)) >= 0.01;
            })
            ->sortBy([
                ['level', 'asc'],
                ['code', 'asc'],
            ])
            ->values();
    }
}
