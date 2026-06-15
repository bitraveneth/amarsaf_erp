export function registerPlStatement(Alpine) {
    Alpine.data('plStatement', (config = {}) => ({
        sections: {
            revenue: true,
            cogs: true,
            opex: true,
        },
        expandedGroups: {},

        init() {
            const keys = config.sectionKeys || ['revenue', 'cogs', 'opex'];
            keys.forEach((key) => {
                this.sections[key] = true;
            });

            const expand = config.defaultExpanded?.length
                ? config.defaultExpanded
                : (config.groupCodes || []);

            expand.forEach((code) => {
                this.expandedGroups[code] = true;
            });
        },

        isSectionOpen(key) {
            return !!this.sections[key];
        },

        toggleSection(key) {
            this.sections[key] = !this.sections[key];
        },

        isGroupExpanded(code) {
            return !!this.expandedGroups[code];
        },

        toggleGroup(code) {
            this.expandedGroups[code] = !this.expandedGroups[code];
        },

        /** Financial expenses (6300) sits after operating profit — always show as its own line. */
        isStandaloneGroup(groupCode) {
            return groupCode === '6300';
        },

        isGroupRowVisible(groupCode, sectionKey) {
            if (this.isStandaloneGroup(groupCode)) {
                return true;
            }

            return this.isSectionOpen(sectionKey);
        },

        isAccountRowVisible(groupCode, sectionKey) {
            if (!this.isGroupExpanded(groupCode)) {
                return false;
            }

            if (this.isStandaloneGroup(groupCode)) {
                return true;
            }

            return this.isSectionOpen(sectionKey);
        },
    }));
}
