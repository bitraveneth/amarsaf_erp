<div {{ $attributes->merge(['class' => 'flex flex-col items-end gap-1.5']) }}>
    <div class="w-full">
        {{ $crud ?? $slot }}
    </div>

    @isset($extra)
        <div class="flex w-full min-w-[9.5rem] flex-col gap-1">
            {{ $extra }}
        </div>
    @endisset
</div>
