<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with(['account', 'payableAccount'])
            ->ordered()
            ->get();

        $expenseAccounts = Account::postable()
            ->where('type', 'expense')
            ->orderBy('code')
            ->get();

        $payableAccounts = Account::postable()
            ->where('type', 'liability')
            ->orderBy('code')
            ->get();

        return view('admin.finance.expense_categories.index', compact(
            'categories',
            'expenseAccounts',
            'payableAccounts'
        ));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('is_group', false)->where('type', 'expense')),
            ],
            'payable_account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('is_group', false)->where('type', 'liability')),
            ],
            'default_payment_type' => 'required|in:bank,cash,payable',
            'default_payment_account_key' => 'required|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $expenseCategory->update([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.expense-categories.index')
            ->with('status', 'Expense category mapping updated.');
    }
}
