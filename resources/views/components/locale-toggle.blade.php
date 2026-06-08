<div
    {{ $attributes->merge(['class' => 'locale-toggle']) }}
    role="group"
    aria-label="Language"
    x-data
>
    <button
        type="button"
        class="locale-toggle__btn"
        :class="{ 'is-active': ($store.learningLang?.code || '{{ app()->getLocale() }}') === 'en' }"
        :disabled="$store.learningLang?.switching"
        @click="$store.learningLang.setLocale('en')"
        aria-pressed="{{ app()->getLocale() === 'en' ? 'true' : 'false' }}"
    >
        EN
    </button>
    <span class="locale-toggle__sep" aria-hidden="true">|</span>
    <button
        type="button"
        class="locale-toggle__btn"
        :class="{ 'is-active': ($store.learningLang?.code || '{{ app()->getLocale() }}') === 'bn' }"
        :disabled="$store.learningLang?.switching"
        @click="$store.learningLang.setLocale('bn')"
        aria-pressed="{{ app()->getLocale() === 'bn' ? 'true' : 'false' }}"
    >
        BD
    </button>
</div>
