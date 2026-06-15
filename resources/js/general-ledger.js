export function registerGeneralLedger(Alpine) {
    Alpine.data('ledgerDetail', (config = {}) => ({
        pickerOpen: false,
        query: '',
        lineQuery: '',
        selectedId: config.selectedId ?? null,
        accounts: config.accounts ?? [],
        periodParams: config.periodParams ?? {},
        action: config.action ?? '',

        get filteredAccounts() {
            const needle = this.query.trim().toLowerCase();

            if (needle === '') {
                return this.accounts;
            }

            return this.accounts.filter((account) => {
                return account.label.toLowerCase().includes(needle)
                    || account.code.toLowerCase().includes(needle)
                    || account.name.toLowerCase().includes(needle);
            });
        },

        get selectedAccount() {
            return this.accounts.find((account) => account.id === this.selectedId) ?? null;
        },

        pickAccount(id) {
            this.selectedId = Number(id);
            this.pickerOpen = false;
            this.query = '';

            const form = this.$refs.accountForm;
            if (! form) {
                return;
            }

            form.querySelector('[name="account_id"]').value = id;
            form.submit();
        },

        lineVisible(searchText) {
            const needle = this.lineQuery.trim().toLowerCase();
            if (needle === '') {
                return true;
            }

            return (searchText ?? '').toLowerCase().includes(needle);
        },
    }));
}
