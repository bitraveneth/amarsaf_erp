@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Expenses</h1>
                <p>Daily, weekly, or monthly expenses by category.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.expenses.create') }}" class="button-primary">Add expense</a>
            </div>
        </header>

        <form method="GET" action="{{ route('admin.expenses.index') }}" class="inline-filters">
            <label>
                From
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}">
            </label>
            <label>
                To
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}">
            </label>
            <label>
                Category
                <select name="category">
                    <option value="">All</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="button-secondary">Filter</button>
        </form>

        <p class="panel-note">
            Total in period: <strong>{{ number_format($total, 2) }}</strong>
        </p>

        @if($expenses->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($expense->category) }}</td>
                            <td>{{ $expense->description ?? '—' }}</td>
                            <td>{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->reference ?? '—' }}</td>
                            <td>{{ ucfirst($expense->status) }}</td>
                            <td>
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this expense? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $expenses->links() }}
        @else
            <p class="panel-note">No expenses recorded for this period.</p>
        @endif
    </section>
</div>
@endsection

