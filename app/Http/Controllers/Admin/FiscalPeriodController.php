<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use App\Services\Accounting\FiscalPeriodService;
use Illuminate\Http\Request;

class FiscalPeriodController extends Controller
{
    public function __construct(protected FiscalPeriodService $periods)
    {
    }

    public function index(Request $request)
    {
        $this->periods->ensureCurrentYear();

        $fiscalYears = FiscalYear::with('periods')->orderByDesc('start_date')->get();
        $selectedYear = $request->query('year')
            ? $fiscalYears->firstWhere('name', $request->query('year'))
            : $fiscalYears->first();

        return view('admin.finance.periods.index', compact('fiscalYears', 'selectedYear'));
    }

    public function close(AccountingPeriod $period)
    {
        $this->periods->closePeriod($period);

        return back()->with('status', $period->name . ' closed.');
    }

    public function open(AccountingPeriod $period)
    {
        $this->periods->openPeriod($period);

        return back()->with('status', $period->name . ' reopened.');
    }
}
