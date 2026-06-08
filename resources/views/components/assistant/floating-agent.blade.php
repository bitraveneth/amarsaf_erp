@auth
@php
    $assistantContext = [
        'source' => request()->routeIs('admin.learning-hub') ? 'learning-hub' : 'erp',
        'module_slug' => request()->routeIs('admin.learning-hub') ? request()->query('module') : null,
        'lesson_index' => null,
        'locale' => app()->getLocale() === 'bn' ? 'bn' : 'en',
        'role' => 'all',
    ];
@endphp
<div class="erp-assistant-root print-hidden"
     x-data="erpAssistantBot"
     data-bootstrap-url="{{ route('admin.assistant.bootstrap') }}"
     data-ask-url="{{ route('admin.assistant.ask') }}"
     data-initial-context='@json($assistantContext)'>

    {{-- Restore chip when dismissed --}}
    <button type="button"
            x-show="dismissed"
            x-cloak
            class="erp-assistant-restore"
            @click="restore()"
            aria-label="Show Ask Saf AI">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
        </svg>
        <span class="erp-assistant-restore__label">Saf AI</span>
    </button>

    <div x-show="!dismissed" x-cloak class="erp-assistant-shell">
    {{-- Chat panel --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         class="erp-assistant-panel"
         @keydown.escape.window="open && close()">

        <div class="erp-assistant-panel__header">
            <div class="erp-assistant-panel__avatar" aria-hidden="true">
                <svg class="h-6 w-6 text-brand-600 dark:text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="erp-assistant-panel__title" x-text="agentName">Saf AI Assistant</div>
                <div class="erp-assistant-panel__status">
                    <span class="erp-assistant-panel__dot" aria-hidden="true"></span>
                    <span x-text="statusLabel">Online · live data</span>
                </div>
            </div>
            <button type="button"
                    class="erp-assistant-panel__close"
                    @click="close()"
                    aria-label="Close Saf AI chat">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="erp-assistant-panel__messages" x-ref="messages">
            <div class="erp-assistant-message erp-assistant-message--agent" x-show="messages.length === 0 && loading">
                <div class="erp-assistant-message__bubble">
                    <p class="whitespace-pre-wrap">Connecting to live ERP data...</p>
                </div>
            </div>

            <template x-for="(msg, index) in messages" :key="index">
                <div class="erp-assistant-message" :class="msg.role === 'user' ? 'erp-assistant-message--user' : 'erp-assistant-message--agent'">
                    <div class="erp-assistant-message__bubble">
                        <p class="whitespace-pre-wrap" x-text="msg.text"></p>
                        <template x-if="msg.links && msg.links.length">
                            <div class="erp-assistant-message__links">
                                <template x-for="(link, linkIndex) in msg.links" :key="linkIndex">
                                    <a :href="link.url"
                                       class="erp-assistant-message__link"
                                       x-text="link.label"></a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="erp-assistant-message erp-assistant-message--agent">
                <div class="erp-assistant-message__bubble erp-assistant-typing" aria-label="Assistant is typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <div class="erp-assistant-panel__chips" x-show="(quickAsks.length || suggestions.length) && !loading">
            <template x-for="(item, chipIndex) in (suggestions.length ? suggestions : quickAsks).slice(0, 6)" :key="chipIndex">
                <button type="button"
                        class="erp-assistant-chip"
                        @click="askQuick(item)"
                        x-text="item.label"></button>
            </template>
        </div>

        <form class="erp-assistant-panel__input" @submit.prevent="sendMessage()">
            <input type="text"
                   class="erp-assistant-input"
                   placeholder="Ask about revenue, profit, orders..."
                   x-model="draft"
                   :disabled="loading"
                   autocomplete="off">
            <button type="submit"
                    class="erp-assistant-send"
                    :disabled="loading || !draft.trim()"
                    aria-label="Send message">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>

    {{-- Launcher --}}
    <div class="erp-assistant-fab-wrap">
        <button type="button"
                class="erp-assistant-fab-dismiss"
                @click.stop="dismiss()"
                title="Hide Ask Saf AI"
                aria-label="Hide Ask Saf AI">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <button type="button"
                class="erp-assistant-fab"
                :class="{ 'erp-assistant-fab--open': open }"
                @click="toggle()"
                :aria-expanded="open"
                :aria-label="open ? 'Close Saf AI chat' : 'Ask Saf AI'">
        <span class="erp-assistant-fab__glow" aria-hidden="true"></span>

        <span x-show="!open" class="erp-assistant-fab__state">
            <span class="erp-assistant-fab__icon-wrap" aria-hidden="true">
                <svg class="erp-assistant-fab__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
            </span>
            <span class="erp-assistant-fab__copy">
                <span class="erp-assistant-fab__eyebrow">Ask</span>
                <span class="erp-assistant-fab__brand">Saf AI</span>
            </span>
            <span class="erp-assistant-fab__status" aria-hidden="true"></span>
        </span>

        <span x-show="open" x-cloak class="erp-assistant-fab__state erp-assistant-fab__state--close">
            <span class="erp-assistant-fab__icon-wrap erp-assistant-fab__icon-wrap--close" aria-hidden="true">
                <svg class="erp-assistant-fab__icon erp-assistant-fab__icon--close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
            <span class="erp-assistant-fab__copy">
                <span class="erp-assistant-fab__eyebrow">Close</span>
                <span class="erp-assistant-fab__brand">Saf AI</span>
            </span>
        </span>
        </button>
    </div>
    </div>
</div>
@endauth
