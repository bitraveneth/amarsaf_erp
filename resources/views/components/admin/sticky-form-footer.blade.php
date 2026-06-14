@props([
    'formId' => null,
])

<div {{ $attributes->merge(['class' => 'sticky bottom-0 z-20 mt-6 border-t border-gray-200 bg-white/95 py-3 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/95']) }}>
    <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
        {{ $slot }}
    </div>
</div>
