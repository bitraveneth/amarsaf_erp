const COMPENSATION_FIELDS = ['base_salary', 'bonus', 'ta_allowances', 'da_allowances', 'commission', 'overtime_pay'];

export function initSalaryDistributionForm() {
    const totalDisplay = document.querySelector('.total-compensation');
    if (!totalDisplay || !document.getElementById('base_salary')) {
        return;
    }

    const currencyPrefix = totalDisplay.closest('[data-currency-prefix]')?.dataset.currencyPrefix
        || document.querySelector('[data-salary-distribution-form]')?.dataset.currencyPrefix
        || '';

    function calculateTotal() {
        let total = 0;

        COMPENSATION_FIELDS.forEach((id) => {
            const input = document.getElementById(id);
            if (input) {
                total += parseFloat(input.value) || 0;
            }
        });

        if (totalDisplay) {
            totalDisplay.textContent = `${currencyPrefix.trim()} ${total.toFixed(2)}`;
        }
    }

    COMPENSATION_FIELDS.forEach((id) => {
        document.getElementById(id)?.addEventListener('input', calculateTotal);
    });

    calculateTotal();
}
