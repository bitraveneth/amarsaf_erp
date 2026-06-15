export function registerTrialBalance(Alpine) {
    Alpine.data('trialBalance', (config = {}) => ({
        expanded: {},

        init() {
            (config.groupIds ?? []).forEach((id) => {
                this.expanded[id] = false;
            });
        },

        isExpanded(id) {
            return !!this.expanded[id];
        },

        toggle(id) {
            this.expanded[id] = !this.expanded[id];
        },

        allAncestorsExpanded(ancestorIds) {
            return ancestorIds.every((id) => this.isExpanded(id));
        },

        expandAll() {
            Object.keys(this.expanded).forEach((id) => {
                this.expanded[id] = true;
            });
        },

        collapseAll() {
            Object.keys(this.expanded).forEach((id) => {
                this.expanded[id] = false;
            });
        },
    }));
}
