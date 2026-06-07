@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ $packagingType->name }}
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if($packagingType->unit)
                    Unit: {{ $packagingType->unit }}
                @else
                    No unit label defined
                @endif
            </p>
            @if($packagingType->description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $packagingType->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.packaging.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Packaging
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.packaging.edit', $packagingType)" />
                <x-admin.action-delete
                    :action="route('admin.packaging.destroy', $packagingType)"
                    confirm="Delete this packaging type? This action cannot be undone."
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Linked products</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $packagingType->products_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Conversions from</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $packagingType->conversionsFrom->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Conversions to</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $packagingType->conversionsTo->count() }}</p>
        </div>
    </div>

    @if($packagingType->conversionsFrom->isNotEmpty() || $packagingType->conversionsTo->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Unit conversions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Direction</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Factor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($packagingType->conversionsFrom as $conversion)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $packagingType->name }} → {{ $conversion->toType->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($conversion->factor, 4) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $conversion->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                        @foreach($packagingType->conversionsTo as $conversion)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $conversion->fromType->name ?? '—' }} → {{ $packagingType->name }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($conversion->factor, 4) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $conversion->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="rounded-lg bg-blue-light-50 p-4 border border-blue-light-100 dark:bg-blue-light-500/10 dark:border-blue-light-500/20">
        <p class="text-sm text-blue-light-800 dark:text-blue-light-300">
            Created {{ $packagingType->created_at->format('d M Y') }} · Last updated {{ $packagingType->updated_at->diffForHumans() }}
        </p>
    </div>
</div>
@endsection
