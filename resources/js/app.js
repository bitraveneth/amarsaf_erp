import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar groups
    const toggles = document.querySelectorAll('[data-collapse-toggle]');

    toggles.forEach((button) => {
        const group = button.closest('[data-collapsible]');
        if (!group) {
            return;
        }

        const icon = button.querySelector('.sidebar-toggle-icon');

        const openIcon = button.dataset.openIcon ?? '−';
        const closedIcon = button.dataset.closedIcon ?? '+';

        const syncState = (isExpanded) => {
            button.setAttribute('aria-expanded', isExpanded.toString());
            if (icon) {
                icon.textContent = isExpanded ? openIcon : closedIcon;
            }
        };

        const initialExpanded = !group.classList.contains('collapsed');
        syncState(initialExpanded);

        button.addEventListener('click', () => {
            const collapsed = group.classList.toggle('collapsed');
            syncState(!collapsed);
        });
    });

    const setupDropdown = (containerSelector, toggleSelector, openClass) => {
        const container = document.querySelector(containerSelector);
        if (!container) {
            return;
        }
        const toggle = container.querySelector(toggleSelector);
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            container.classList.toggle(openClass);
        });

        document.addEventListener('click', () => {
            container.classList.remove(openClass);
        });

        container.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    };

    // Header user dropdown
    setupDropdown('.header-user', '.header-user-toggle', 'header-user--open');

    // Header alerts dropdown
    setupDropdown('.header-alert', '.header-alert-toggle', 'header-alert--open');

    // Dismiss flash messages
    document.querySelectorAll('.flash-close').forEach((btn) => {
        btn.addEventListener('click', () => {
            const flash = btn.closest('.flash');
            if (flash) {
                flash.remove();
            }
        });
    });

    // Tag input with chips + suggestions
    document.querySelectorAll('[data-tag-input]').forEach((wrapper) => {
        const field = wrapper.querySelector('[data-tag-field]');
        const hidden = wrapper.querySelector('[data-tag-hidden]');
        const chipsContainer = wrapper.querySelector('[data-tag-chips]');
        const suggestionsBox = wrapper.querySelector('[data-tag-suggestions]');

        if (!field || !hidden || !chipsContainer || !suggestionsBox) {
            return;
        }

        const allOptions = JSON.parse(wrapper.dataset.tagOptions || '[]');
        let tags = [];

        const normalise = (value) => value.trim().replace(/\s+/g, ' ').toLowerCase();

        const renderChips = () => {
            chipsContainer.innerHTML = '';
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.textContent = tag;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'tag-chip-remove';
                remove.textContent = '×';
                remove.addEventListener('click', () => {
                    tags.splice(index, 1);
                    syncHidden();
                    renderChips();
                });

                chip.appendChild(remove);
                chipsContainer.appendChild(chip);
            });
        };

        const syncHidden = () => {
            hidden.value = tags.join(', ');
        };

        const addTag = (value) => {
            const trimmed = value.trim();
            if (!trimmed) return;

            const normalised = normalise(trimmed);
            if (tags.some((t) => normalise(t) === normalised)) {
                return;
            }

            tags.push(trimmed);
            syncHidden();
            renderChips();
        };

        const parseInitial = () => {
            if (!hidden.value) return;
            hidden.value.split(',').forEach((piece) => addTag(piece));
        };

        const renderSuggestions = (query) => {
            const q = normalise(query);
            const existing = new Set(tags.map((t) => normalise(t)));

            const matches = allOptions.filter((opt) => {
                const n = normalise(opt);
                if (existing.has(n)) {
                    return false;
                }
                return !q || n.includes(q);
            });

            suggestionsBox.innerHTML = '';
            if (!matches.length || !q) {
                suggestionsBox.classList.remove('open');
                return;
            }

            matches.forEach((match) => {
                const item = document.createElement('div');
                item.className = 'tag-suggestion';
                item.textContent = match;
                item.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    addTag(match);
                    field.value = '';
                    suggestionsBox.classList.remove('open');
                });
                suggestionsBox.appendChild(item);
            });

            suggestionsBox.classList.add('open');
        };

        field.addEventListener('input', () => {
            const value = field.value;
            if (value.includes(',')) {
                value.split(',').forEach((piece) => addTag(piece));
                field.value = '';
                suggestionsBox.classList.remove('open');
                return;
            }
            renderSuggestions(value);
        });

        field.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === 'Tab') {
                if (field.value.trim() !== '') {
                    event.preventDefault();
                    addTag(field.value);
                    field.value = '';
                    suggestionsBox.classList.remove('open');
                }
            } else if (event.key === 'Backspace' && field.value === '' && tags.length) {
                tags.pop();
                syncHidden();
                renderChips();
            }
        });

        field.addEventListener('blur', () => {
            if (field.value.trim() !== '') {
                addTag(field.value);
                field.value = '';
            }
            setTimeout(() => {
                suggestionsBox.classList.remove('open');
            }, 150);
        });

        parseInitial();
        renderChips();
    });
});
