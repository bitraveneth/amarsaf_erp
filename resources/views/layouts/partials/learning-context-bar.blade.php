@if(! request()->routeIs('admin.learning-hub') && Route::has('admin.learning-hub'))
    @php
        $learningUi = $learningUi ?? \App\Support\Learning\LearningHubRepository::ui();
        $learningHubUrl = $learningHubUrl ?? route('admin.learning-hub');
        $learningModuleSlug = $learningModuleSlug ?? null;
        $learningModule = $learningModule ?? null;
        if ($learningModuleSlug && ! $learningModule) {
            $learningModule = \App\Support\Learning\LearningHubRepository::module($learningModuleSlug);
        }
        if ($learningModuleSlug) {
            $learningHubUrl = route('admin.learning-hub', ['module' => $learningModuleSlug]);
        }
    @endphp

    <div class="learning-context-bar print-hidden" x-data="{ ui: @js($learningUi) }">
        <div class="learning-context-bar__inner">
            <div class="learning-context-bar__icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>

            <div class="learning-context-bar__copy">
                @if($learningModule)
                    <p class="learning-context-bar__label" x-text="$store.learningLang.pick(ui.guide_this_page)"></p>
                    <p class="learning-context-bar__title" x-text="$store.learningLang.pick(@js($learningModule['title'] ?? ['en' => '', 'bn' => '']))"></p>
                @else
                    <p class="learning-context-bar__label" x-text="$store.learningLang.pick(ui.learning_hub)"></p>
                    <p class="learning-context-bar__title" x-text="$store.learningLang.pick(ui.hub_subtitle)"></p>
                @endif
            </div>

            <div class="learning-context-bar__actions">
                <a href="{{ $learningHubUrl }}" class="learning-context-bar__btn learning-context-bar__btn--primary">
                    <span x-text="$store.learningLang.pick(ui.learning_hub)"></span>
                </a>
                @if(Route::has('admin.client-guide'))
                    <a href="{{ route('admin.client-guide') }}" class="learning-context-bar__btn">
                        <span x-text="$store.learningLang.pick(ui.full_manual)"></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
