<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Services\Accounting\ExpensePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(protected ExpensePostingService $posting)
    {
    }

    private const VALID_STATUSES = [
        Expense::STATUS_RECORDED,
        Expense::STATUS_REVIEWED,
    ];

    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = Expense::with('expenseCategory')->whereBetween('date', [$from, $to]);

        if ($category = $request->query('category')) {
            $query->where(function ($q) use ($category) {
                $q->where('category', $category)
                    ->orWhereHas('expenseCategory', fn ($cq) => $cq->where('code', $category));
            });
        }

        $expenses = $query->orderByDesc('date')->paginate(20)->withQueryString();
        $total = (clone $query)->sum('amount');

        $categories = ExpenseCategory::active()->ordered()->get();

        return view('admin.finance.expenses.index', compact('expenses', 'from', 'to', 'total', 'categories'));
    }

    public function create()
    {
        return view('admin.finance.expenses.create', [
            'expense' => new Expense([
                'payment_type' => ExpenseCategory::PAYMENT_BANK,
                'payment_account_key' => 'bank_default',
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
            'payment_type' => 'required|in:bank,cash,payable',
            'payment_account_key' => 'required|string|max:100',
            'analytic_label' => 'nullable|string|max:100',
        ]);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        $data['category'] = $category->code;

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'expenseCategories' => ExpenseCategory::active()->ordered()->with('account')->get(),
            'expenseAccounts' => Account::postable()->where('type', 'expense')->orderBy('code')->get(),
            'paymentAccountKeys' => [
                'bank_default' => 'Default bank (BRAC)',
                'bank_brac' => 'BRAC Bank',
                'bank_scb' => 'SCB Bank',
                'cash_in_hand' => 'Cash in hand',
                'petty_cash' => 'Petty cash',
            ],
        ];
    }

    protected function hasPostedJournal(Expense $expense): bool
    {
        return JournalEntry::query()
            ->where('journal_type', 'expense')
            ->where('source_type', Expense::class)
            ->where('source_id', $expense->id)
            ->exists();
    }
}
