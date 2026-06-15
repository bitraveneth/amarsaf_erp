export function registerReportsLibrary(Alpine) {
    Alpine.data('reportsLibrary', (config = {}) => ({
        groups: config.groups ?? [],
        featured: config.featured ?? [],
        query: '',
        category: 'all',
        expanded: {},

        init() {
            this.$watch('query', () => this.syncExpandedForFilter());
            this.$watch('category', () => this.syncExpandedForFilter());
        },

        get categories() {
            return [
                { key: 'all', label: 'All reports', count: this.allReports.length },
                ...this.groups.map((group) => ({
                    key: group.key,
                    label: group.label,
                    count: group.reports.length,
                })),
            ];
        },

        get allReports() {
            return this.groups.flatMap((group) => group.reports);
        },

        get visibleGroups() {
            const normalizedQuery = this.query.trim().toLowerCase();

            return this.groups
                .filter((group) => this.category === 'all' || group.key === this.category)
                .map((group) => ({
                    ...group,
                    reports: group.reports.filter((report) => this.matchesQuery(report, normalizedQuery)),
                }))
                .filter((group) => group.reports.length > 0);
        },

        isGroupOpen(key) {
            return !!this.expanded[key];
        },

        toggleGroup(key) {
            this.expanded[key] = !this.expanded[key];
        },

        expandAll() {
            this.visibleGroups.forEach((group) => {
                this.expanded[group.key] = true;
            });
        },

        collapseAll() {
            this.expanded = {};
        },

        syncExpandedForFilter() {
            const hasFilter = this.query.trim() !== '' || this.category !== 'all';

            if (!hasFilter) {
                this.expanded = {};
                return;
            }

            this.visibleGroups.forEach((group) => {
                this.expanded[group.key] = true;
            });
        },

        matchesQuery(report, normalizedQuery) {
            if (normalizedQuery === '') {
                return true;
            }

            const haystack = [
                report.title,
                report.hint,
                report.description,
                report.badge,
                report.category,
                ...(report.bullets ?? []),
            ].join(' ').toLowerCase();

            return haystack.includes(normalizedQuery);
        },

        setCategory(key) {
            this.category = key;
        },
    }));
}
