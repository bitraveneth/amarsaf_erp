<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Models\PaymentInstrument;
use App\Models\PaymentMethodCategory;
use App\Notifications\SystemAlertNotification;
use App\Services\Accounting\ExpensePostingService;
use App\Services\Accounting\UnpostedExpenseNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use ResolvesDashboardPeriod;

    public function __construct(protected ExpensePostingService $posting)
    {
    }

    private const VALID_STATUSES = [
        Expense::STATUS_RECORDED,
        Expense::STATUS_REVIEWED,
    ];

    public function index(Request $request)
    {
        $rangeOptions = [
            'today' => 'Today',
            '7d' => 'This week',
            'month' => 'This month',
            'year' => 'This year',
            'lifetime' => 'Lifetime',
            'custom' => 'Custom range',
        ];

        $range = (string) $request->query('range', '');
        if ($range === '' && ($request->filled('from') || $request->filled('to'))) {
            $range = 'custom';
        }
        if (! array_key_exists($range, $rangeOptions)) {
            $range = 'month';
        }

        $today = Carbon::today();
        [$from, $to] = match ($range) {
            'today' => [$today->copy(), $today->copy()],
            '7d' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'lifetime' => [Carbon::parse('2000-01-01'), $today->copy()],
            'custom' => [
                $request->filled('from') ? Carbon::parse($request->query('from')) : $today->copy()->startOfMonth(),
                $request->filled('to') ? Carbon::parse($request->query('to')) : $today->copy()->endOfMonth(),
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy(), $from->copy()];
        }

        $periodLabel = $range === 'lifetime'
            ? 'Lifetime through ' . $to->format('d M Y')
            : $this->dashboardPeriodLabel($from, $to);

        $query = Expense::with('expenseCategory')->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        if ($category = $request->query('category')) {
            $query->where(function ($q) use ($category) {
                $q->where('category', $category)
                    ->orWhereHas('expenseCategory', fn ($cq) => $cq->where('code', $category));
            });
        }

        $expenses = $query->orderByDesc('date')->paginate(20)->withQueryString();
        $total = (clone $query)->sum('amount');

        $categories = ExpenseCategory::active()->ordered()->get();

        return view('admin.finance.expenses.index', compact(
            'expenses',
            'from',
            'to',
            'total',
            'categories',
            'range',
            'rangeOptions',
            'periodLabel',
        ));
    }

    public function create()
    {
        $defaultInstrument = PaymentInstrument::query()
            ->active()
            ->where('is_default', true)
            ->whereHas('category', fn ($q) => $q->where('kind', PaymentMethodCategory::KIND_BANK))
            ->first()
            ?? PaymentInstrument::query()->active()->orderBy('id')->first();

        return view('admin.finance.expenses.create', [
            'expense' => new Expense([
                'payment_type' => ExpenseCategory::PAYMENT_BANK,
                'payment_account_key' => $defaultInstrument?->slug ?? 'bank_brac',
                'payment_instrument_id' => $defaultInstrument?->id,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $expense = Expense::create($data);
            $this->posting->sync($expense);
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        $expense->load('expenseCategory');

        return view('admin.finance.expenses.edit', [
            'expense' => $expense,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            $this->posting->sync($expense->fresh());
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($this->hasPostedJournal($expense)) {
            return redirect()
                ->route('admin.expenses.index')
                ->withErrors([
                    'expense' => 'Posted expenses cannot be deleted. Preserve the audit trail and record a correcting adjustment instead.',
                ]);
        }

        DB::transaction(function () use ($expense) {
            app(\App\Services\Accounting\AccountingService::class)
                ->deleteByJournalTypeAndSource('expense', Expense::class, $expense->id);
            $expense->delete();
        });

        return redirect()->route('admin.expenses.index')->with('status', 'Expense deleted.');
    }

    public function syncLedger(Request $request)
    {
        $missingOnly = $request->input('scope') === 'missing';
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $expenses = Expense::query()
            ->where('amount', '>', 0)
            ->when(! $missingOnly, function ($query) use ($from, $to) {
                $query->whereDate('date', '>=', $from->toDateString())
                    ->whereDate('date', '<=', $to->toDateString());
            })
            ->orderBy('id')
            ->get();

        $synced = 0;
        $skipped = 0;
        $failed = 0;
        $failure = null;

        foreach ($expenses as $expense) {
            if ($this->hasPostedJournal($expense)) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(fn () => $this->posting->sync($expense));
                $synced++;
            } catch (\Throwable $exception) {
                $failed++;
                $failure ??= $exception->getMessage();
            }
        }

        $message = match (true) {
            $synced > 0 && $failed === 0 => "{$synced} expense(s) posted to the ledger.",
            $synced > 0 => "{$synced} expense(s) posted. {$failed} could not be posted. {$failure}",
            $failed > 0 => "No expenses were posted. {$failure}",
            default => 'All of these expenses already have ledger entries.',
        };

        app(UnpostedExpenseNotice::class)->forget();
        $this->notifyLedgerSync($request->user(), $message, $failed === 0 && $synced > 0);
        if ($failed === 0) {
            $this->clearLedgerGapNotifications();
        }

        return redirect()
            ->to($request->input('redirect', route('admin.reports.pl', $request->only(['range', 'from', 'to']))))
            ->with('status', $message);
    }

    protected function notifyLedgerSync($user, string $message, bool $success): void
    {
        if (! $user || ! \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }

        $user->notify(new SystemAlertNotification([
            'key' => 'expense_ledger_sync_'.now()->format('YmdHis'),
            'title' => $success ? 'Expenses posted to the ledger' : 'Expense sync needs attention',
            'message' => $message,
            'variant' => $success ? 'success' : 'warning',
            'source' => 'Finance',
            'link' => route('admin.reports.pl'),
        ]));
    }

    protected function clearLedgerGapNotifications(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }

        \Illuminate\Notifications\DatabaseNotification::query()
            ->where('type', SystemAlertNotification::class)
            ->whereNull('read_at')
            ->where('data', 'like', '%unposted_expenses_%')
            ->update(['read_at' => now()]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('is_group', false)->where('type', 'expense')),
            ],
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'payment_type' => 'nullable|in:bank,cash,payable',
            'payment_instrument_id' => [
                Rule::requiredIf(fn () => $request->input('payment_type') !== ExpenseCategory::PAYMENT_PAYABLE),
                'nullable',
                'exists:payment_instruments,id',
            ],
            'payment_account_key' => 'nullable|string|max:100',
            'analytic_label' => 'nullable|string|max:100',
        ]);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        $data['category'] = $category->code;

        if (($data['payment_type'] ?? null) === ExpenseCategory::PAYMENT_PAYABLE) {
            $data['payment_instrument_id'] = null;
            $data['payment_account_key'] = $data['payment_account_key'] ?? 'bank_default';
            $data['payment_type'] = ExpenseCategory::PAYMENT_PAYABLE;
        } else {
            $instrument = PaymentInstrument::with('category')->findOrFail($data['payment_instrument_id']);
            $data['payment_instrument_id'] = $instrument->id;
            $data['payment_type'] = $instrument->category?->postingPaymentType() ?? ExpenseCategory::PAYMENT_BANK;
            $data['payment_account_key'] = $instrument->slug;
        }

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'expenseCategories' => ExpenseCategory::active()->ordered()->with('account')->get(),
            'expenseAccounts' => Account::postable()->where('type', 'expense')->orderBy('code')->get(),
            'paymentCategories' => PaymentMethodCategory::active()
                ->ordered()
                ->with(['instruments' => fn ($q) => $q->active()->ordered()->with('account')])
                ->get(),
        ];
    }

    protected function hasPostedJournal(Expense $expense): bool
    {
        return JournalEntry::query()
            ->where('journal_type', 'expense')
            ->where('source_type', Expense::class)
            ->where('source_id', $expense->id)
            ->where('status', 'posted')
            ->exists();
    }
}
