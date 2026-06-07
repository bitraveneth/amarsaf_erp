@props([
    'type',
    'id',
    'label' => null,
    'compact' => false,
])

@php
    $previewUrl = route('admin.documents.preview', ['type' => $type, 'id' => $id]);
    $pdfUrl = route('admin.documents.pdf', ['type' => $type, 'id' => $id]);
    $docLabel = $label ?: (__('documents.types.' . str_replace('-', '_', $type)) ?? __('documents.actions.open_document'));
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }}>
    <a href="{{ $previewUrl }}"
       target="_blank"
       rel="noopener"
       class="{{ $compact ? 'inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300' : 'inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300' }}">
        <svg class="{{ $compact ? 'h-3.5 w-3.5' : 'h-4 w-4' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        {{ __('documents.actions.print') }}
    </a>
    <a href="{{ $pdfUrl }}"
       class="{{ $compact ? 'inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white' : 'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700' }}">
        <svg class="{{ $compact ? 'h-3.5 w-3.5' : 'h-4 w-4' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        {{ __('documents.actions.download_pdf') }}
    </a>
    @if(!$compact)
        <span class="sr-only">{{ $docLabel }}</span>
    @endif
</div>
