@props([
    'label',
    'name',
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'erp-form-field']) }}>
    <label for="{{ $name }}" class="erp-label">
        {{ $label }}
        @if($required)
            <span class="text-error-500" aria-hidden="true">*</span>
        @endif
    </label>

    {{ $slot }}

    @if($hint)
        <p class="erp-form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="erp-form-error">{{ $message }}</p>
    @enderror
</div>
