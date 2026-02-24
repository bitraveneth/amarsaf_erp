@extends('layouts.app')

@section('content')
<div class="dashboard-shell space-y-6">
    {{-- Summary Panel --}}
    <section class="panel rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <header class="panel-header mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white">Salary distributions</h1>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Per-employee salary, allowances, and commission for a period.</p>
            </div>
            <form method="GET" action="{{ route('admin.salary-distributions.index') }}" class="flex flex-wrap items-end gap-3">
                <label class="flex flex-col gap-1.5">
                    <span class="text-theme-xs font-medium text-gray-700 dark:text-gray-300">Month</span>
                    <input type="month" 
                           name="month" 
                           value="{{ request('month', $month->format('Y-m')) }}"
                           class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                </label>
                <label class="flex flex-col gap-1.5">
                    <span class="text-theme-xs font-medium text-gray-700 dark:text-gray-300">Employee</span>
                    <select name="employee_id" 
                            class="h-10 min-w-[160px] rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                        <option value="" class="dark:bg-gray-900">All employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string)request('employee_id') === (string)$employee->id ? 'selected' : '' }} class="dark:bg-gray-900">
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" 
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.5 17.5L13.3333 13.3333M15 8.33333C15 12.0152 12.0152 15 8.33333 15C4.65144 15 1.66667 12.0152 1.66667 8.33333C1.66667 4.65144 4.65144 1.66667 8.33333 1.66667C12.0152 1.66667 15 4.65144 15 8.33333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Filter
                </button>
            </form>
        </header>

        <div class="master-grid grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-800/50">
                <p class="text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total distributed</p>
                <h3 class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">
                    {{ number_format($total, 2) }} {{ config('app.currency', 'BDT') }}
                </h3>
                <span class="mt-1 inline-block text-theme-xs text-gray-500 dark:text-gray-400">For selected period</span>
            </article>
            
            {{-- Additional metrics you might want to add --}}
            @if(isset($employeeCount))
            <article class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-800/50">
                <p class="text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Employees</p>
                <h3 class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">
                    {{ $employeeCount }}
                </h3>
                <span class="mt-1 inline-block text-theme-xs text-gray-500 dark:text-gray-400">Active this period</span>
            </article>
            @endif
            
            @if(isset($averageDistribution))
            <article class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-800/50">
                <p class="text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Average</p>
                <h3 class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">
                    {{ number_format($averageDistribution, 2) }} {{ config('app.currency', 'BDT') }}
                </h3>
                <span class="mt-1 inline-block text-theme-xs text-gray-500 dark:text-gray-400">Per employee</span>
            </article>
            @endif
        </div>
    </section>

    {{-- Distributions Table Panel --}}
    <section class="panel rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <header class="panel-header mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-title-sm font-semibold text-gray-900 dark:text-white">Distributions</h2>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">One row per employee per period.</p>
            </div>
            <a href="{{ route('admin.salary-distributions.create') }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Add distribution
            </a>
        </header>

        @if($rows->isNotEmpty())
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="data-table w-full border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <th class="whitespace-nowrap px-4 py-3 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Employee</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Base salary (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Bonus (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">TA (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">DA (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Commission (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total (BDT)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Document</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($rows as $row)
                            @php
                                $totalRow = ($row->base_salary ?? 0)
                                    + ($row->bonus ?? 0)
                                    + ($row->ta_allowances ?? 0)
                                    + ($row->da_allowances ?? 0)
                                    + ($row->commission ?? 0);
                            @endphp
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-brand-100 text-theme-xs font-medium text-brand-700 flex items-center justify-center dark:bg-brand-500/20 dark:text-brand-400">
                                            {{ strtoupper(substr($row->employee?->name ?? '?', 0, 2)) }}
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $row->employee?->name ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-400 dark:text-white">
                                            <path d="M2.5 6.66667H17.5M5 3.33333H15M4.16667 16.6667H15.8333C16.7538 16.6667 17.5 15.9205 17.5 15V5C17.5 4.07953 16.7538 3.33333 15.8333 3.33333H4.16667C3.24619 3.33333 2.5 4.07953 2.5 5V15C2.5 15.9205 3.24619 16.6667 4.16667 16.6667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        {{ optional($row->period_start)->format('M d, Y') }} – {{ optional($row->period_end)->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-theme-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($row->base_salary, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-theme-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($row->bonus, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-theme-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($row->ta_allowances, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-theme-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($row->da_allowances, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-theme-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($row->commission, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-brand-700 dark:text-brand-400">
                                    {{ number_format($totalRow, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @php
                                        $methodColors = [
                                            'bank' => ['bg' => 'success-50', 'text' => 'success-700', 'dark' => ['bg' => 'success-500/20', 'text' => 'success-400']],
                                            'cash' => ['bg' => 'warning-50', 'text' => 'warning-700', 'dark' => ['bg' => 'warning-500/20', 'text' => 'warning-400']],
                                            'check' => ['bg' => 'blue-light-50', 'text' => 'blue-light-700', 'dark' => ['bg' => 'blue-light-500/20', 'text' => 'blue-light-400']],
                                        ];
                                        $method = $row->payment_method ?? 'bank';
                                        $colors = $methodColors[$method] ?? $methodColors['bank'];
                                    @endphp
                                    <span class="inline-flex rounded-full bg-{{ $colors['bg'] }} px-2.5 py-1 text-theme-xs font-medium text-{{ $colors['text'] }} dark:bg-{{ $colors['dark']['bg'] }} dark:text-{{ $colors['dark']['text'] }}">
                                        {{ ucfirst($method) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if($row->document_path)
                                        <a href="{{ asset('storage/'.$row->document_path) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-theme-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 13.3333V4.16667M10 13.3333L7.5 10.8333M10 13.3333L12.5 10.8333M17.5 13.3333V15.8333C17.5 16.7538 16.7538 17.5 15.8333 17.5H4.16667C3.24619 17.5 2.5 16.7538 2.5 15.8333V13.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            View
                                        </a>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="max-w-[200px] px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">
                                    <span class="line-clamp-2" title="{{ $row->remarks }}">
                                        {{ $row->remarks ?: '—' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.salary-distributions.edit', $row) }}" 
                                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.1667 2.5L17.5 5.83333M2.5 14.1667L11.6667 5L15 8.33333L5.83333 17.5H2.5V14.1667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.salary-distributions.destroy', $row) }}" 
                                              method="POST" 
                                              class="inline-flex"
                                              onsubmit="return confirm('Delete this salary distribution record? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-error-700 shadow-theme-xs transition hover:bg-error-50 hover:text-error-800 dark:border-gray-700 dark:bg-gray-800 dark:text-error-400 dark:hover:bg-error-500/20">
                                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2.5 5H4.16667H17.5M15.8333 5V16.6667C15.8333 17.5 15 18.3333 14.1667 18.3333H5.83333C5 18.3333 4.16667 17.5 4.16667 16.6667V5M6.66667 5V3.33333C6.66667 2.5 7.5 1.66667 8.33333 1.66667H11.6667C12.5 1.66667 13.3333 2.5 13.3333 3.33333V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6">
                {{ $rows->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 py-12 dark:border-gray-800">
                <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="text-theme-lg font-medium text-gray-700 dark:text-gray-300">No salary distributions found</p>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or add a new distribution.</p>
                <a href="{{ route('admin.salary-distributions.create') }}" 
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add your first distribution
                </a>
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Payment method badge colors - using your theme colors */
.bg-success-50 { background-color: #ecfdf3; }
.bg-warning-50 { background-color: #fffaeb; }
.bg-blue-light-50 { background-color: #f0f9ff; }

.text-success-700 { color: #067647; }
.text-warning-700 { color: #b54708; }
.text-blue-light-700 { color: #026aa2; }

.dark .dark\:bg-success-500\/20 { background-color: rgba(18, 183, 106, 0.2); }
.dark .dark\:bg-warning-500\/20 { background-color: rgba(247, 144, 9, 0.2); }
.dark .dark\:bg-blue-light-500\/20 { background-color: rgba(11, 165, 236, 0.2); }

.dark .dark\:text-success-400 { color: #47cd89; }
.dark .dark\:text-warning-400 { color: #fdb022; }
.dark .dark\:text-blue-light-400 { color: #36bffa; }
</style>
@endpush
