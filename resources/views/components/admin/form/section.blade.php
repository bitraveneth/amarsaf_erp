@props([
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'erp-form-section']) }}>
    <div class="erp-form-section__head">
        <h3 class="erp-h3">{{ $title }}</h3>
        @if($description)
            <p class="erp-form-section__desc">{{ $description }}</p>
        @endif
    </div>

    <div class="erp-form-section__body">
        {{ $slot }}
    </div>
</section>
