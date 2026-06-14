<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\LedgerEntry;
use App\Services\Accounting\AccountPathService;
use App\Services\Accounting\AccountResolver;
use App\Services\Accounting\ChartOfAccountsExportService;
use App\Services\Accounting\ChartOfAccountsSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    use ResolvesDashboardPeriod;

    public function index(Request $request, ChartOfAccountsExportService $coaExport, ChartOfAccountsSummaryService $coaSummary)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $periodLabel = $this->dashboardPeriodLabel($from, $to);
        $rangeOptions = $this->dashboardRangeOptions();
        $currencyCode = config('app.currency', 'BDT');

        $selectedRoot = $request->query('root');
        $validRoots = Account::REPORT_ROOTS;

        if (! in_array($selectedRoot, $validRoots, true)) {
            $selectedRoot = null;
        }

        $selectedType = $coaExport->normalizeType($request->query('type'));
        $viewMode = in_array($request->query('view'), ['structure', 'balance'], true)
            ? $request->query('view')
            : 'structure';

        $allAccounts = Account::query()->orderBy('code')->get();
        $accountMap = $allAccounts->keyBy('id');
        $groupAccounts = Account::query()->groups()->orderBy('code')->get();

        $roots = Account::query()
            ->roots()
            ->when($selectedRoot, fn ($q) => $q->where('report_root', $selectedRoot))
            ->with(['children.children.children.children'])
            ->orderBy('code')
            ->get();

        $roots = $coaExport->pruneTreeByType($roots, $selectedType);

        $stats = [
            'total' => $allAccounts->count(),
            'groups' => $allAccounts->where('is_group', true)->count(),
            'ledgers' => $allAccounts->where('is_group', false)->count(),
            'active' => $allAccounts->where('is_active', true)->count(),
            'inactive' => $allAccounts->where('is_active', false)->count(),
        ];

        $structureTypeCards = collect(['asset', 'liability', 'equity', 'income', 'expense'])->mapWithKeys(function (string $type) use ($allAccounts) {
            $subset = $allAccounts->where('type', $type);

            return [$type => [
                'count' => $subset->count(),
                'groups' => $subset->where('is_group', true)->count(),
                'ledgers' => $subset->where('is_group', false)->count(),
            ]];
        });

        $balancePeriod = [];
        $balanceClosing = [];
        $netAssets = 0.0;
        $typeCards = $structureTypeCards;

        if ($viewMode === 'balance') {
            $summary = $coaSummary->summarize($from, $to, $allAccounts);
            $typeCards = collect($summary['type_cards']);
            $balancePeriod = $summary['period'];
            $balanceClosing = $summary['closing'];
            $netAssets = $summary['net_assets'];
        }

        $groupIds = $allAccounts->where('is_group', true)->pluck('id')->values()->all();

        $filterQuery = array_filter(array_merge(
            [
                'view' => $viewMode,
                'type' => $selectedType,
                'root' => $selectedRoot,
            ],
            $viewMode === 'balance' ? [
                'range' => $range,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ] : []
        ));

        return view('admin.finance.accounts', compact(
            'roots',
            'allAccounts',
            'accountMap',
            'groupAccounts',
            'selectedRoot',
            'validRoots',
            'stats',
            'groupIds',
            'selectedType',
            'viewMode',
            'structureTypeCards',
            'typeCards',
            'from',
            'to',
            'range',
            'rangeOptions',
            'periodLabel',
            'currencyCode',
            'balancePeriod',
            'balanceClosing',
            'netAssets',
            'filterQuery',
            'coaSummary',
        ));
    }

    public function create(Request $request)
    {
        $parent = $request->query('parent_id')
            ? Account::groups()->find($request->query('parent_id'))
            : null;

        return view('admin.finance.accounts_edit', [
            'account' => new Account(['is_group' => false, 'is_active' => true, 'parent_id' => $parent?->id]),
            'groupAccounts' => Account::query()->groups()->orderBy('code')->get(),
            'parent' => $parent,
        ]);
    }

    public function store(Request $request, AccountPathService $paths, AccountResolver $resolver)
    {
        $data = $this->validated($request);
        $parent = ! empty($data['parent_id']) ? Account::find($data['parent_id']) : null;

        if ($parent && ! $parent->is_group) {
            return back()->withErrors(['parent_id' => 'Parent must be a group account.'])->withInput();
        }

        if (! $data['is_group'] && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name'], '_');
        }

        if ($data['is_group']) {
            $data['slug'] = null;
        }

        $account = Account::create($data);
        $paths->assignHierarchy($account, $parent);
        $account->save();
        $paths->refresh($account);
        $resolver->forget();

        return redirect()->route('admin.accounts.index')->with('status', 'Account created.');
    }

    public function show(Account $account)
    {
        $account->load(['parent', 'children']);

        $journalLineCount = JournalEntryLine::where('account_id', $account->id)->count();
        $legacyLineCount = LedgerEntry::where('account', $account->name)->count();
        $balance = $account->rollupBalance();

        return view('admin.finance.accounts_show', compact('account', 'journalLineCount', 'legacyLineCount', 'balance'));
    }

    public function edit(Account $account)
    {
        return view('admin.finance.accounts_edit', [
            'account' => $account,
            'groupAccounts' => Account::query()->groups()->where('id', '!=', $account->id)->orderBy('code')->get(),
            'parent' => $account->parent,
        ]);
    }

    public function update(Request $request, Account $account, AccountPathService $paths, AccountResolver $resolver)
    {
        $data = $this->validated($request, $account);

        if ($data['name'] !== $account->name && (
            LedgerEntry::where('account', $account->name)->exists()
            || JournalEntryLine::where('account_id', $account->id)->exists()
        )) {
            return redirect()
                ->route('admin.accounts.edit', $account)
                ->withErrors(['name' => 'Accounts referenced by ledger entries cannot be renamed.']);
        }

        if (! empty($data['parent_id']) && (int) $data['parent_id'] === (int) $account->id) {
            return back()->withErrors(['parent_id' => 'An account cannot be its own parent.'])->withInput();
        }

        $parent = ! empty($data['parent_id']) ? Account::find($data['parent_id']) : null;
        if ($parent && ! $parent->is_group) {
            return back()->withErrors(['parent_id' => 'Parent must be a group account.'])->withInput();
        }

        if ($data['is_group']) {
            $data['slug'] = null;
        } elseif (empty($data['slug']) && ! $account->slug) {
            $data['slug'] = Str::slug($data['name'], '_');
        }

        $account->update($data);
        $paths->assignHierarchy($account->fresh(), $parent);
        $account->save();
        $paths->refresh($account);
        $resolver->forget();

        return redirect()->route('admin.accounts.index')->with('status', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        if ($account->children()->exists()) {
            return redirect()
                ->route('admin.accounts.index')
                ->with('error', 'Remove or reassign child accounts before deleting a group.');
        }

        if (LedgerEntry::where('account', $account->name)->exists() || JournalEntryLine::where('account_id', $account->id)->exists()) {
            return redirect()
                ->route('admin.accounts.index')
                ->with('error', 'Account is referenced by ledger entries and cannot be deleted.');
        }

        $account->delete();

        return redirect()->route('admin.accounts.index')->with('status', 'Account deleted.');
    }

    protected function validated(Request $request, ?Account $account = null): array
    {
        $accountId = $account?->id ?? $request->route('account')?->id;

        return $request->validate([
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts', 'code')->ignore($accountId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')->ignore($accountId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('accounts', 'slug')->ignore($accountId),
            ],
            'type' => 'required|in:asset,liability,equity,income,expense',
            'report_root' => ['nullable', Rule::in(Account::REPORT_ROOTS)],
            'is_group' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_group' => $request->boolean('is_group'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
