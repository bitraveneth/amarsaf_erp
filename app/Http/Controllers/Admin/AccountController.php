<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('code')->get();
        return view('admin.finance.accounts', compact('accounts'));
    }

    public function create()
    {
        return view('admin.finance.accounts_edit', ['account' => new Account()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Account::create($data);

        return redirect()->route('admin.accounts.index')->with('status', 'Account created.');
    }

    public function edit(Account $account)
    {
        return view('admin.finance.accounts_edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validated($request);
        $account->update($data);

        return redirect()->route('admin.accounts.index')->with('status', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        $account->delete();

        return redirect()->route('admin.accounts.index')->with('status', 'Account deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'is_active' => 'sometimes|boolean',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}

