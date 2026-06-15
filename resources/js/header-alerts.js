export function initHeaderAlerts() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function variantStyles(variant) {
        const map = {
            error: {
                ring: 'border-error-300/70 bg-error-50/70 dark:border-error-700/60 dark:bg-error-500/10',
                dot: 'bg-error-500',
                label: 'text-error-700 dark:text-error-300',
            },
            warning: {
                ring: 'border-warning-300/70 bg-warning-50/70 dark:border-warning-700/60 dark:bg-warning-500/10',
                dot: 'bg-warning-500',
                label: 'text-warning-700 dark:text-warning-300',
            },
            success: {
                ring: 'border-success-300/70 bg-success-50/70 dark:border-success-700/60 dark:bg-success-500/10',
                dot: 'bg-success-500',
                label: 'text-success-700 dark:text-success-300',
            },
            info: {
                ring: 'border-brand-300/70 bg-brand-50/70 dark:border-brand-700/60 dark:bg-brand-500/10',
                dot: 'bg-brand-500',
                label: 'text-brand-700 dark:text-brand-300',
            },
        };

        return map[variant] || {
            ring: 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50',
            dot: 'bg-gray-400',
            label: 'text-gray-600 dark:text-gray-300',
        };
    }

    function updateHeaderCounts(unreadCount) {
        document.querySelectorAll('.js-header-alert-count').forEach((badge) => {
            badge.textContent = String(unreadCount);
            badge.classList.toggle('hidden', unreadCount === 0);
        });

        document.querySelectorAll('.js-header-alert-active-count').forEach((text) => {
            text.textContent = String(unreadCount);
        });
    }

    function renderHeaderAlerts(root, alerts, unreadCount) {
        const menu = root.querySelector('.header-alert-menu');
        if (!menu) {
            return;
        }

        updateHeaderCounts(unreadCount);

        const footer = menu.querySelector('.border-t');
        let list = menu.querySelector('.js-header-alert-list');
        let empty = menu.querySelector('.js-header-alert-empty');

        if (alerts.length > 0) {
            if (!list) {
                list = document.createElement('ul');
                list.className = 'js-header-alert-list max-h-[430px] space-y-2 overflow-y-auto p-4 text-[13px] text-gray-700 dark:text-gray-300';
                if (footer) {
                    menu.insertBefore(list, footer);
                } else {
                    menu.appendChild(list);
                }
            }

            list.innerHTML = alerts.map((alert) => {
                const styles = variantStyles(alert.variant || 'info');
                const source = escapeHtml(alert.source || 'System');
                const message = escapeHtml(alert.message || '');
                const timeLabel = escapeHtml(alert.time_label || 'Now');
                const openUrl = escapeHtml(alert.open_url || '#');

                return `
                    <li class="js-header-alert-item">
                        <a href="${openUrl}" class="header-alert-action group block rounded-xl border px-3 py-2.5 transition hover:shadow-sm ${styles.ring}">
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full ${styles.dot}"></span>
                                    <span class="text-[11px] font-semibold uppercase tracking-wide ${styles.label}">${source}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">${timeLabel}</span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-brand-600 dark:group-hover:text-brand-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="leading-5 text-gray-800 dark:text-gray-100">${message}</p>
                        </a>
                    </li>
                `;
            }).join('');

            if (empty) {
                empty.remove();
            }
            return;
        }

        if (list) {
            list.remove();
        }

        if (!empty) {
            empty = document.createElement('div');
            empty.className = 'js-header-alert-empty p-4';
            empty.innerHTML = '<p class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-[13px] text-gray-500 dark:border-gray-700 dark:bg-white/5 dark:text-gray-400">No current alerts.</p>';
            if (footer) {
                menu.insertBefore(empty, footer);
            } else {
                menu.appendChild(empty);
            }
        }
    }

    function loadHeaderAlerts(root) {
        const url = root.dataset.fetchUrl;
        if (!url || root.dataset.loadingAjax === '1') {
            return Promise.resolve();
        }

        root.dataset.loadingAjax = '1';
        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data || data.success !== true) {
                    return;
                }
                const alerts = Array.isArray(data.alerts) ? data.alerts : [];
                const unreadCount = Number.isInteger(data.unread_count) ? data.unread_count : alerts.length;
                renderHeaderAlerts(root, alerts, unreadCount);
            })
            .catch(() => {})
            .finally(() => {
                root.dataset.loadingAjax = '0';
            });
    }

    document.querySelectorAll('.js-header-alert-root').forEach((root) => {
        loadHeaderAlerts(root);

        const toggle = root.querySelector('.header-alert-toggle');
        if (toggle) {
            toggle.addEventListener('click', () => {
                loadHeaderAlerts(root);
            });
        }

        setInterval(() => {
            if (document.hidden) {
                return;
            }
            loadHeaderAlerts(root);
        }, 60000);
    });
}
