const ASSISTANT_DISMISSED_KEY = 'erpAssistantDismissed';

export function registerAssistantBot(Alpine) {
    Alpine.data('erpAssistantBot', () => ({
        open: false,
        dismissed: false,
        loading: false,
        bootstrapped: false,
        agentName: 'Saf AI Assistant',
        greeting: '',
        quote: '',
        prompt: '',
        quickAsks: [],
        suggestions: [],
        messages: [],
        draft: '',
        bootstrapUrl: '',
        askUrl: '',

        init() {
            const root = this.$root;
            this.bootstrapUrl = root.dataset.bootstrapUrl || '';
            this.askUrl = root.dataset.askUrl || '';

            try {
                this.dismissed = localStorage.getItem(ASSISTANT_DISMISSED_KEY) === '1';
            } catch (error) {
                this.dismissed = false;
            }
        },

        toggle() {
            if (this.dismissed) {
                this.restore();
                return;
            }

            this.open = ! this.open;

            if (this.open) {
                this.ensureBootstrap();
            }
        },

        close() {
            this.open = false;
        },

        dismiss() {
            this.open = false;
            this.dismissed = true;

            try {
                localStorage.setItem(ASSISTANT_DISMISSED_KEY, '1');
            } catch (error) {
                // Ignore storage failures.
            }
        },

        restore() {
            this.dismissed = false;

            try {
                localStorage.removeItem(ASSISTANT_DISMISSED_KEY);
            } catch (error) {
                // Ignore storage failures.
            }
        },

        async ensureBootstrap() {
            if (this.bootstrapped || ! this.bootstrapUrl) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(this.bootstrapUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('Bootstrap failed');
                }

                const payload = await response.json();
                this.agentName = payload.agent_name || this.agentName;
                this.greeting = payload.greeting || '';
                this.quote = payload.quote || '';
                this.prompt = payload.prompt || '';
                this.quickAsks = payload.quick_asks || [];
                this.suggestions = payload.quick_asks || [];
                this.bootstrapped = true;

                if (this.messages.length === 0) {
                    this.messages.push({
                        role: 'agent',
                        text: [this.greeting, this.quote, this.prompt].filter(Boolean).join(' '),
                    });
                }
            } catch (error) {
                this.messages.push({
                    role: 'agent',
                    text: 'I had trouble connecting. Please refresh the page and try again.',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async sendMessage(text) {
            const message = (text ?? this.draft).trim();
            if (! message || this.loading || ! this.askUrl) {
                return;
            }

            this.messages.push({ role: 'user', text: message });
            this.draft = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch(this.askUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ message }),
                });

                if (! response.ok) {
                    throw new Error('Ask failed');
                }

                const payload = await response.json();
                this.messages.push({
                    role: 'agent',
                    text: payload.reply || 'Done.',
                    links: payload.links || [],
                });
                this.suggestions = payload.suggestions || this.quickAsks;
            } catch (error) {
                this.messages.push({
                    role: 'agent',
                    text: 'Sorry, I could not process that request. Please try again.',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        askQuick(item) {
            this.sendMessage(item.message || item.label);
        },

        scrollToBottom() {
            const panel = this.$refs.messages;
            if (panel) {
                panel.scrollTop = panel.scrollHeight;
            }
        },
    }));
}
