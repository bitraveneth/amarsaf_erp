@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $journal->number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $journal->entry_date->format('d M Y') }} · {{ str_replace('_', ' ', $journal->journal_type) }} · <span class="capitalize">{{ $journal->status }}</span></p>
            @if($journal->description)<p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $journal->description }}</p>@endif
        </div>
        <div class="flex flex-wrap gap-2">
            @if($journal->isEditable())
                <a href="{{ route('admin.journals.edit', $journal) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold dark:border-gray-700">Edit</a>
                <form method="POST" action="{{ route('admin.journals.post', $journal) }}">@csrf<button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Post</button></form>
            @endif
            @if($journal->status === 'posted')
                <form method="POST" action="{{ route('admin.journals.reverse', $journal) }}">@csrf<button class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Reverse</button></form>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Account</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Debit</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Credit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($journal->lines as $line)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $line->account->code }} — {{ $line->account->name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $line->description }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ number_format($line->debit, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ number_format($line->credit, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <td colspan="2" class="px-4 py-3 text-sm font-semibold">Totals</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold">{{ number_format($journal->totalDebit(), 2) }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold">{{ number_format($journal->totalCredit(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
