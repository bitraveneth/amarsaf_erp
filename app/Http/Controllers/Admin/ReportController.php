<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function profitAndLoss(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $entries = LedgerEntry::whereBetween('created_at', [$from, $to])->get();

        $sales = $entries->where('account', 'Sales Revenue')->sum('credit');
        $returns = $entries->where('account', 'Sales Returns')->sum('debit');
        $commissions = $entries->where('account', 'Commission Expense')->sum('debit');

        $netSales = $sales - $returns;
        $profit = $netSales - $commissions;

        return view('admin.finance.pl', compact('from', 'to', 'sales', 'returns', 'commissions', 'netSales', 'profit'));
    }

    public function vat(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $entries = LedgerEntry::where('account', 'VAT Payable')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $vatCollected = $entries->sum('credit') - $entries->sum('debit');

        return view('admin.finance.vat', compact('month', 'vatCollected'));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $entries = LedgerEntry::where('created_at', '<=', $asOf)->get();

        $balances = [];
        foreach ($entries as $entry) {
            $account = $entry->account;
            if (!isset($balances[$account])) {
                $balances[$account] = 0;
            }
            $balances[$account] += $entry->debit - $entry->credit;
        }

        $assetsAccounts = Account::where('type', 'asset')->pluck('name')->all() ?: ['Bank', 'Accounts Receivable'];
        $liabilityAccounts = Account::where('type', 'liability')->pluck('name')->all() ?: ['Accounts Payable', 'VAT Payable'];

        $assets = [];
        $liabilities = [];

        foreach ($balances as $account => $amount) {
            if (in_array($account, $assetsAccounts, true)) {
                $assets[$account] = $amount;
            } elseif (in_array($account, $liabilityAccounts, true)) {
                $liabilities[$account] = $amount * -1;
            }
        }

        $totalAssets = array_sum($assets);
        $totalLiabilities = array_sum($liabilities);
        $equity = $totalAssets - $totalLiabilities;

        return view('admin.finance.bs', compact('asOf', 'assets', 'liabilities', 'totalAssets', 'totalLiabilities', 'equity'));
    }

    public function cashflow(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $bankAccounts = Account::where('type', 'asset')->where('code', 'like', '1%')->pluck('name')->all();

        $entries = LedgerEntry::whereIn('account', $bankAccounts ?: ['Bank'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $cashIn = $entries->sum('debit');
        $cashOut = $entries->sum('credit');
        $net = $cashIn - $cashOut;

        return view('admin.finance.cashflow', compact('from', 'to', 'cashIn', 'cashOut', 'net'));
    }
}
