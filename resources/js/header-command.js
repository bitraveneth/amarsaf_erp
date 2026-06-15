function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function initHeaderCommand() {
    const root = document.querySelector('[data-command-root]');

    if (!root) {
        return;
    }

    const commandInput = root.querySelector('[data-command-search]');
    const commandResults = root.querySelector('[data-command-results]');

    if (!commandInput || !commandResults) {
        return;
    }

    let items = [];

    try {
        items = JSON.parse(commandInput.dataset.searchIndex || '[]');
    } catch (error) {
        items = [];
    }

    let filtered = [];
    let activeIndex = -1;

    const closeResults = () => {
        commandResults.classList.add('hidden');
        commandResults.innerHTML = '';
        filtered = [];
        activeIndex = -1;
    };

    const renderResults = () => {
        if (!commandInput.value.trim() || !filtered.length) {
            closeResults();

            return;
        }

        commandResults.innerHTML = `
            <ul class="shell-command__results-list custom-scrollbar">
                ${filtered
                    .map(
                        (item, index) => `
                    <li
                        class="command-item shell-command__result ${
                            index === activeIndex ? 'is-active' : ''
                        }"
                        data-index="${index}"
                        data-path="${escapeHtml(item.path)}"
                    >
                        <div class="shell-command__result-text">
                            <span class="shell-command__result-label">${escapeHtml(item.label)}</span>
                            <span class="shell-command__result-group">${escapeHtml(item.group)}</span>
                        </div>
                        <span class="shell-command__result-hint">Go →</span>
                    </li>`
                    )
                    .join('')}
            </ul>
        `;

        commandResults.classList.remove('hidden');

        commandResults.querySelectorAll('.command-item').forEach((element) => {
            element.addEventListener('mousedown', (event) => {
                event.preventDefault();
                const path = element.dataset.path;

                if (path) {
                    window.location.href = path;
                }
            });
        });
    };

    const updateFiltered = async () => {
        const query = commandInput.value.trim().toLowerCase();

        if (!query) {
            closeResults();

            return;
        }

        filtered = items.filter((item) => {
            const haystack = `${item.label} ${item.group} ${item.path}`.toLowerCase();

            return haystack.includes(query);
        });

        const entitySearchUrl = commandInput.dataset.entitySearchUrl;

        if (entitySearchUrl && query.length >= 2) {
            try {
                const response = await fetch(`${entitySearchUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (response.ok) {
                    const payload = await response.json();
                    const entityItems = (payload.data || []).map((item) => ({
                        label: item.label,
                        group: item.group,
                        path: item.path,
                    }));

                    filtered = filtered.concat(entityItems);
                }
            } catch (error) {
                // Keep menu matches only when entity search fails.
            }
        }

        filtered = filtered.slice(0, 10);
        activeIndex = filtered.length ? 0 : -1;
        renderResults();
    };

    commandInput.addEventListener('input', () => {
        updateFiltered();
    });

    commandInput.addEventListener('focus', () => {
        if (commandInput.value.trim()) {
            updateFiltered();
        }
    });

    commandInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            if (filtered.length) {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % filtered.length;
                renderResults();
            }
        } else if (event.key === 'ArrowUp') {
            if (filtered.length) {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + filtered.length) % filtered.length;
                renderResults();
            }
        } else if (event.key === 'Enter') {
            if (filtered.length && activeIndex >= 0) {
                event.preventDefault();
                const item = filtered[activeIndex];

                if (item?.path) {
                    window.location.href = item.path;
                }
            }
        } else if (event.key === 'Escape') {
            event.preventDefault();
            commandInput.blur();
            closeResults();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeResults();
        }
    });

    document.addEventListener('keydown', (event) => {
        const isMac = window.erpPlatform === 'mac';
        const meta = isMac ? event.metaKey : event.ctrlKey;

        if (meta && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            commandInput.focus();
            commandInput.select();
        }
    });
}
