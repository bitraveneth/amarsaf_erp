@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Recurring fleet charges" subtitle="Monthly truck rent/lease — generate fleet expenses in one click.">
        <x-slot:actions>
            <a href="{{ route('admin.fleet-recurring.create') }}" class="erp-btn-secondary">Add schedule</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))<div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800">{{ session('status') }}</div>@endif

    <form action="{{ route('admin.fleet-recurring.generate') }}" method="POST" class="rounded-xl border border-brand-200 bg-brand-50/50 p-4 flex flex-wrap items-end gap-3 dark:border-brand-500/30 dark:bg-brand-500/5">
        @csrf
        <div>
            <label class="mb-1 block text-xs text-gray-500">Generate for month</label>
            <input type="month" name="for_month" value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
        <button type="submit" class="erp-btn-primary">Generate expenses</button>
    </form>

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead><tr><th>Vehicle</th><th>Type</th><th class="is-right">Amount</th><th>Day</th><th>Last run</th><th class="is-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($charges as $charge)
                        <tr>
                            <td>{{ $charge->vehicle->name }}</td>
                            <td>{{ \App\Models\FleetExpense::types()[$charge->expense_type] ?? $charge->expense_type }}</td>
                            <td class="is-right">BDT {{ number_format($charge->amount, 0) }}</td>
                            <td>{{ $charge->day_of_month }}</td>
                            <td>{{ $charge->last_generated_for?->format('M Y') ?? '—' }}</td>
                            <td class="is-right">
                                <form action="{{ route('admin.fleet-recurring.destroy', $charge) }}" method="POST" class="inline" onsubmit="return confirm('Remove this schedule?');">@csrf @method('DELETE')<button type="submit" class="erp-btn-action-danger">Remove</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">No recurring charges configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($charges->hasPages())<div class="px-6 py-4">{{ $charges->links() }}</div>@endif
    </div>
</div>
@endsection
