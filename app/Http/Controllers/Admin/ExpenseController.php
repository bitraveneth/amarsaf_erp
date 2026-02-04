<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = Expense::whereBetween('date', [$from, $to]);

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $expenses = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $total = (clone $query)->sum('amount');

        $categories = Expense::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.finance.expenses.index', compact('expenses', 'from', 'to', 'total', 'categories'));
    }

    public function create()
    {
        return view('admin.finance.expenses.create', [
            'expense' => new Expense(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.finance.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request);

        $expense->update($data);

        return redirect()->route('admin.expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('status', 'Expense deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
        ]);
    }
}
