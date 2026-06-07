@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">KYC Document Types</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Standard categories for agent compliance uploads — NID, Trade License, BIN, TIN, and custom types.
            </p>
        </div>
        <a href="{{ route('admin.agents.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Back to Agents
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Document Type</h3>
        </div>
        <form action="{{ route('admin.kyc-document-types.store') }}" method="POST" class="p-6">
            @csrf
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Type name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. VAT Certificate, Bank Statement"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                    Add type
                </button>
            </div>
        </form>
    </div>

    @if($types->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Document type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($types as $type)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('admin.kyc-document-types.destroy', $type) }}" method="POST"
                                          onsubmit="return confirm('Delete document type {{ $type->name }}?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="erp-btn-action-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $types->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
