@extends('layouts.app')

@php
    $docsRoot = base_path('docs');
    $manifestPath = $docsRoot . DIRECTORY_SEPARATOR . 'manual-manifest.php';
    $manualFiles = file_exists($manifestPath) ? require $manifestPath : ['client-user-guide.md'];
    $manualSections = [];
    $manualToc = [];

    foreach ($manualFiles as $relativePath) {
        $path = $docsRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (! file_exists($path)) {
            continue;
        }
        $markdown = file_get_contents($path);
        $title = 'Section';
        if (preg_match('/^#\s+(.+)$/m', $markdown, $m)) {
            $title = trim($m[1]);
        }
        $anchor = 'guide-' . md5($relativePath);
        $manualToc[] = ['title' => $title, 'anchor' => $anchor, 'path' => $relativePath];
        $manualSections[] = [
            'path' => $relativePath,
            'anchor' => $anchor,
            'html' => \Illuminate\Support\Str::markdown($markdown),
        ];
    }
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Client Manual</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Plain-English guides for your team — products, manufacturing, and more.
                    Detailed module manuals live in the <code class="text-xs">docs/modules/</code> folder for printing or sharing.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.help') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Back to Help
                </a>
                <button onclick="window.print()" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                    Print
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contents</h2>
            <nav class="mt-3 flex flex-wrap gap-2">
                @foreach($manualToc as $item)
                    <a href="#{{ $item['anchor'] }}"
                       class="inline-flex rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:text-brand-300">
                        {{ $item['title'] }}
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="overflow-x-auto p-6 sm:p-8">
            @foreach($manualSections as $section)
                <article id="{{ $section['anchor'] }}" class="guide-prose text-sm leading-7 text-gray-700 dark:text-gray-300">
                    {!! $section['html'] !!}
                </article>
                @if(! $loop->last)
                    <hr class="guide-section-break my-10 border-gray-200 dark:border-gray-700">
                @endif
            @endforeach
        </div>
    </div>
</div>

<style>
    .guide-prose h1, .guide-prose h2, .guide-prose h3, .guide-prose h4 {
        color: inherit;
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .guide-prose h1 { font-size: 1.75rem; }
    .guide-prose h2 { font-size: 1.35rem; }
    .guide-prose h3 { font-size: 1.1rem; }
    .guide-prose p, .guide-prose ul, .guide-prose ol { margin: 0.55rem 0; }
    .guide-prose ul, .guide-prose ol { padding-left: 1.2rem; }
    .guide-prose code {
        background: rgba(59, 130, 246, 0.12);
        border-radius: 0.35rem;
        padding: 0.1rem 0.35rem;
        font-size: 0.85em;
    }
    .guide-prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 0.8rem 0 1rem;
        min-width: 680px;
    }
    .guide-prose th, .guide-prose td {
        border: 1px solid rgba(148, 163, 184, 0.25);
        padding: 0.55rem 0.65rem;
        text-align: left;
        vertical-align: top;
    }
    .guide-prose th {
        background: rgba(51, 65, 85, 0.12);
        font-weight: 700;
    }
    .guide-prose pre {
        overflow-x: auto;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        background: rgba(51, 65, 85, 0.08);
        font-size: 0.78rem;
        line-height: 1.45;
    }
    .dark .guide-prose pre {
        background: rgba(15, 23, 42, 0.55);
    }
    .guide-section-break {
        border-style: dashed;
    }
</style>
@endsection
