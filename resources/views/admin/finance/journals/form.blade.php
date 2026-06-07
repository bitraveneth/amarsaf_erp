@extends('layouts.app')

@section('content')
@php
    $lineRows = old('lines', $journal->exists ? $journal->lines->map(fn ($line) => [
        'account_id' => $line->account_id,
        'debit' => $line->debit,
        'credit' => $line->credit,
        'description' => $line->description,
    ])->all() : [
        ['account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
        ['account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
    ]);
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $journal->exists ? 'Edit journal' : 'New manual journal' }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Debits must equal credits before posting.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $journal->exists ? route('admin.journals.update', $journal) : route('admin.journals.store') }}" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @if($journal->exists) @method('PATCH') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Entry date</label>
                <input type="date" name="entry_date" value="{{ old('entry_date', optional($journal->entry_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Description</label>
                <input type="text" name="description" value="{{ old('description', $journal->description) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Account</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Debit</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Credit</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Line note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lineRows as $index => $line)
                        <tr>
                            <td class="px-3 py-2">
                                <select name="lines[{{ $index }}][account_id]" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" required>
                                    <option value="">Select account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((string) old('lines.'.$index.'.account_id', $line['account_id'] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="lines[{{ $index }}][debit]" value="{{ old('lines.'.$index.'.debit', $line['debit'] ?? '') }}" class="w-full rounded-lg border-gray-300 text-right dark:border-gray-700 dark:bg-gray-800"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="lines[{{ $index }}][credit]" value="{{ old('lines.'.$index.'.credit', $line['credit'] ?? '') }}" class="w-full rounded-lg border-gray-300 text-right dark:border-gray-700 dark:bg-gray-800"></td>
                            <td class="px-3 py-2"><input type="text" name="lines[{{ $index }}][description]" value="{{ old('lines.'.$index.'.description', $line['description'] ?? '') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Save draft</button>
            <button type="submit" name="post_now" value="1" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Save & post</button>
            <a href="{{ route('admin.journals.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold dark:border-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection
