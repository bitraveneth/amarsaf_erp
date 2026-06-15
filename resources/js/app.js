import './bootstrap';
import './invoice';
import { registerAssistantBot } from './assistant-bot';
import { registerSystemTour } from './system-tour';
import { registerLearningLang, learningHub } from './learning-hub';
import { registerPlStatement } from './pl-statement';
import { registerReportsLibrary } from './reports-library';
import { registerTrialBalance } from './trial-balance';
import { registerGeneralLedger } from './general-ledger';
import { initHeaderAlerts } from './header-alerts';
import { initHeaderCommand } from './header-command';
import { initBillItems } from './bill-items';
import { registerBomForm } from './bom-form';
import { registerProductionForm, initProductionFormWidgets } from './production-form';
import { initSalaryDistributionForm } from './salary-distribution-form';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.erpUiFontStack = document.documentElement.getAttribute('data-locale') === 'bn'
    ? '"Noto Sans Bengali", Inter, system-ui, sans-serif'
    : 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Ubuntu, Cantarell, "Helvetica Neue", Arial, sans-serif';

const detectClientPlatform = () => {
    const rawPlatform = (
        navigator.userAgentData?.platform ||
        navigator.platform ||
        navigator.userAgent ||
        ''
    ).toLowerCase();

    if (rawPlatform.includes('mac') || rawPlatform.includes('iphone') || rawPlatform.includes('ipad')) {
        return 'mac';
    }

    if (rawPlatform.includes('win')) {
        return 'windows';
    }

    if (rawPlatform.includes('linux') || rawPlatform.includes('x11')) {
        return 'linux';
    }

    return 'other';
};

window.erpPlatform = detectClientPlatform();

document.addEventListener('alpine:init', () => {
    registerSystemTour(Alpine, window.erpTourSteps || []);
    registerAssistantBot(Alpine);
    registerLearningLang(Alpine);
    registerPlStatement(Alpine);
    registerReportsLibrary(Alpine);
    registerTrialBalance(Alpine);
    registerGeneralLedger(Alpine);
    registerBomForm(Alpine);
    registerProductionForm(Alpine);
});

window.learningHub = learningHub;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.dataset.platform = window.erpPlatform;
    if (document.body) {
        document.body.dataset.platform = window.erpPlatform;
    }

    initHeaderAlerts();
    initHeaderCommand();
    initBillItems();
    initProductionFormWidgets();
    initSalaryDistributionForm();

    const dashboardRoot = document.querySelector('[data-dashboard-ajax][data-dashboard-charts]');
    if (dashboardRoot) {
        import('./dashboard-charts').then(({ initDashboardCharts }) => initDashboardCharts());
    }

    if (document.querySelector('[data-dashboard-insights-charts]')) {
        import('./dashboard-insights-charts').then(({ initDashboardInsightsCharts }) => initDashboardInsightsCharts());
    }

    const commandModifier = window.erpPlatform === 'mac' ? '⌘' : 'Ctrl';
    document.querySelectorAll('[data-shortcut-mod]').forEach((el) => {
        el.textContent = commandModifier;
    });
    document.querySelectorAll('[data-command-shortcut], [data-command-shortcut-trigger]').forEach((el) => {
        el.setAttribute('aria-label', `Open command palette (${commandModifier} K)`);
        el.setAttribute('title', `Open command palette (${commandModifier} K)`);
    });


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
        const containers = document.querySelectorAll(containerSelector);
        if (!containers.length) {
            return;
        }

        containers.forEach((container) => {
            const toggle = container.querySelector(toggleSelector);
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                // Close any other open containers of the same type
                containers.forEach((other) => {
                    if (other !== container) {
                        other.classList.remove(openClass);
                    }
                });

                container.classList.toggle(openClass);
            });

            container.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        });

        document.addEventListener('click', () => {
            containers.forEach((container) => container.classList.remove(openClass));
        });
    };

    // Header user dropdown
    setupDropdown('.header-user', '.header-user-toggle', 'header-user--open');

    // Header alerts dropdown
    setupDropdown('.header-alert', '.header-alert-toggle', 'header-alert--open');

    // Header alerts close buttons
    document.querySelectorAll('.header-alert-menu-close').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const container = btn.closest('.header-alert');
            if (container) {
                container.classList.remove('header-alert--open');
            }
        });
    });

    // Warehouse card actions dropdown
    setupDropdown('.warehouse-card-actions', '.warehouse-card-actions-toggle', 'warehouse-card-actions--open');

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

    // Product / material type specific fields
    const typeSelect = document.getElementById('product_type');
    if (typeSelect) {
        const syncProductTypeFields = () => {
            const type = typeSelect.value || 'finished';

            document.querySelectorAll('[data-product-type="finished-only"]').forEach((el) => {
                el.style.display = type === 'finished' ? '' : 'none';
            });
            document.querySelectorAll('[data-product-type="material-only"]').forEach((el) => {
                el.style.display = type === 'finished' ? 'none' : '';
            });

            // For in‑house steps supplier is usually not relevant – disable the field for clarity.
            const supplierInput = document.querySelector('[data-material-supplier]');
            const supplierWrapper = document.querySelector('[data-material-supplier-wrapper]');
            if (supplierInput) {
                if (type === 'inhouse') {
                    supplierInput.value = '';
                    supplierInput.disabled = true;
                    if (supplierWrapper) {
                        supplierWrapper.style.display = 'none';
                    }
                } else {
                    supplierInput.disabled = false;
                    if (supplierWrapper) {
                        supplierWrapper.style.display = '';
                    }
                }
            }

            // Update UOM placeholder by material type
            const uomInput = document.querySelector('[data-material-uom]');
            if (uomInput) {
                if (type === 'raw') {
                    uomInput.placeholder = 'e.g., piece, kg, liter';
                } else if (type === 'service') {
                    uomInput.placeholder = 'e.g., day, trip, shift, service';
                } else if (type === 'inhouse') {
                    uomInput.placeholder = 'e.g., piece, batch, case';
                }
            }
        };

        syncProductTypeFields();
        typeSelect.addEventListener('change', syncProductTypeFields);
    }

    // Preset + custom combo fields (size, volume)
    const wirePresetCombo = (selectId, inputId) => {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) {
            return;
        }

        const customValue = '__custom';

        const syncVisibility = () => {
            if (select.value === customValue) {
                input.style.display = '';
            } else {
                input.style.display = 'none';
                input.value = select.value || '';
            }
        };

        select.addEventListener('change', syncVisibility);
        syncVisibility();
    };

    wirePresetCombo('size_select', 'size');
    wirePresetCombo('volume_ml_select', 'volume_ml');

    // Generate production order number from button
    const orderBtn = document.getElementById('btn-generate-order-number');
    const orderInput = document.getElementById('order_number');
    const batchSelect = document.getElementById('batch_id');
    if (orderBtn && orderInput) {
        orderBtn.addEventListener('click', () => {
            const params = new URLSearchParams();
            if (batchSelect && batchSelect.value) {
                params.set('batch_id', batchSelect.value);
            }

            fetch(`/admin/production/order-number?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.order_number) {
                        orderInput.value = data.order_number;
                    }
                })
                .catch(() => {
                    // Fail silently; server-side auto-generation will still work on submit.
                });
        });
    }

    // Login password visibility toggle
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('toggle-password');
    if (passwordInput && toggleButton) {
        const showIcon = toggleButton.querySelector('.password-toggle-icon-show');
        const hideIcon = toggleButton.querySelector('.password-toggle-icon-hide');

        if (showIcon && hideIcon) {
            toggleButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                showIcon.style.display = isHidden ? 'none' : 'block';
                hideIcon.style.display = isHidden ? 'block' : 'none';
                toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        }
    }
});
