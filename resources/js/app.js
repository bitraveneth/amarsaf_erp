import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
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
});
