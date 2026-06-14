<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Posts demo journal entries against the hierarchical chart of accounts
 * (sales, purchases, manufacturing, payroll, and operating expenses).
 */
class ChartOfAccountsDemoJournalSeeder extends Seeder
{
    public const JOURNAL_TYPE = 'coa_demo';

    public function run(): void
    {
        if ($this->shouldSkip()) {
            return;
        }

        $accounting = app(AccountingService::class);
        $today = Carbon::today();
        $startMonth = $today->copy()->subMonths(6)->startOfMonth();

        try {
            $this->seedOpeningBalances($accounting, $startMonth->copy()->subDay());
        } catch (InvalidArgumentException $e) {
            $this->command?->warn('ChartOfAccountsDemoJournalSeeder skipped opening balances: ' . $e->getMessage());

            return;
        }

        for ($offset = 0; $offset < 6; $offset++) {
            $month = $startMonth->copy()->addMonths($offset);
            $factor = 1 + ($offset * 0.07);

            $this->seedMonth($accounting, $month, $factor);
        }
    }

    protected function shouldSkip(): bool
    {
        if (Account::query()->ledgers()->whereNotNull('slug')->count() < 20) {
            return true;
        }

        return JournalEntry::query()->where('journal_type', self::JOURNAL_TYPE)->exists();
    }

    protected function seedOpeningBalances(AccountingService $accounting, Carbon $date): void
    {
        $this->post($accounting, $date, 'Demo opening balances — chart of accounts', [
            ['account_key' => 'bank_brac', 'debit' => 850000, 'credit' => 0],
            ['account_key' => 'raw_materials', 'debit' => 320000, 'credit' => 0],
            ['account_key' => 'finished_goods', 'debit' => 210000, 'credit' => 0],
            ['account_key' => 'retained_earnings', 'debit' => 0, 'credit' => 1380000],
        ]);
    }

    protected function seedMonth(AccountingService $accounting, Carbon $month, float $factor): void
    {
        $salesNet = round(180000 * $factor, 2);
        $vat = round($salesNet * 0.15, 2);
        $gross = round($salesNet + $vat, 2);
        $receipt = round($gross * 0.72, 2);
        $purchaseNet = round(95000 * $factor, 2);
        $inputVat = round($purchaseNet * 0.15, 2);
        $purchaseGross = round($purchaseNet + $inputVat, 2);
        $consumption = round(78000 * $factor, 2);
        $salaries = round(42000 * $factor, 2);
        $utilities = round(8500 * $factor, 2);
        $commission = round(12000 * $factor, 2);
        $marketing = round(6500 * $factor, 2);
        $delivery = round(4800 * $factor, 2);
        $supplierPayment = round($purchaseGross * 0.55, 2);

        $invoiceDay = $month->copy()->day(5);
        $receiptDay = $month->copy()->day(12);
        $purchaseDay = $month->copy()->day(8);
        $productionDay = $month->copy()->day(15);
        $payrollDay = $month->copy()->day(28);
        $label = $month->format('M Y');

        $this->post($accounting, $invoiceDay, "Demo sales invoice — {$label}", [
            ['account_key' => 'trade_debtors', 'debit' => $gross, 'credit' => 0],
            ['account_key' => 'product_sales', 'debit' => 0, 'credit' => $salesNet],
            ['account_key' => 'vat_payable', 'debit' => 0, 'credit' => $vat],
        ]);

        if ($receipt > 0) {
            $this->post($accounting, $receiptDay, "Demo customer receipt — {$label}", [
                ['account_key' => 'bank_brac', 'debit' => $receipt, 'credit' => 0],
                ['account_key' => 'trade_debtors', 'debit' => 0, 'credit' => $receipt],
            ]);
        }

        $this->post($accounting, $purchaseDay, "Demo RM purchase — {$label}", [
            ['account_key' => 'raw_materials', 'debit' => $purchaseNet, 'credit' => 0],
            ['account_key' => 'input_vat', 'debit' => $inputVat, 'credit' => 0],
            ['account_key' => 'trade_creditors', 'debit' => 0, 'credit' => $purchaseGross],
        ]);

        $this->post($accounting, $productionDay, "Demo RM to WIP — {$label}", [
            ['account_key' => 'wip', 'debit' => $consumption, 'credit' => 0],
            ['account_key' => 'raw_materials', 'debit' => 0, 'credit' => $consumption],
        ]);

        $this->post($accounting, $productionDay->copy()->addDay(), "Demo WIP to finished goods — {$label}", [
            ['account_key' => 'finished_goods', 'debit' => $consumption, 'credit' => 0],
            ['account_key' => 'wip', 'debit' => 0, 'credit' => $consumption],
        ]);

        $this->post($accounting, $productionDay->copy()->addDays(2), "Demo manufacturing consumption — {$label}", [
            ['account_key' => 'raw_material_consumption', 'debit' => $consumption, 'credit' => 0],
            ['account_key' => 'finished_goods', 'debit' => 0, 'credit' => $consumption],
        ]);

        $this->post($accounting, $payrollDay, "Demo payroll — {$label}", [
            ['account_key' => 'salaries_wages', 'debit' => $salaries, 'credit' => 0],
            ['account_key' => 'bank_brac', 'debit' => 0, 'credit' => $salaries],
        ]);

        $this->post($accounting, $payrollDay, "Demo utilities — {$label}", [
            ['account_key' => 'utilities_expense', 'debit' => $utilities, 'credit' => 0],
            ['account_key' => 'bank_brac', 'debit' => 0, 'credit' => $utilities],
        ]);

        $this->post($accounting, $payrollDay, "Demo commission accrual — {$label}", [
            ['account_key' => 'commission_expense', 'debit' => $commission, 'credit' => 0],
            ['account_key' => 'commission_payable', 'debit' => 0, 'credit' => $commission],
        ]);

        $this->post($accounting, $payrollDay, "Demo marketing spend — {$label}", [
            ['account_key' => 'marketing_expense', 'debit' => $marketing, 'credit' => 0],
            ['account_key' => 'bank_brac', 'debit' => 0, 'credit' => $marketing],
        ]);

        $this->post($accounting, $payrollDay, "Demo delivery expense — {$label}", [
            ['account_key' => 'delivery_expense', 'debit' => $delivery, 'credit' => 0],
            ['account_key' => 'bank_brac', 'debit' => 0, 'credit' => $delivery],
        ]);

        if ($supplierPayment > 0) {
            $this->post($accounting, $payrollDay->copy()->subDays(3), "Demo supplier payment — {$label}", [
                ['account_key' => 'trade_creditors', 'debit' => $supplierPayment, 'credit' => 0],
                ['account_key' => 'bank_brac', 'debit' => 0, 'credit' => $supplierPayment],
            ]);
        }
    }

    protected function post(AccountingService $accounting, Carbon $date, string $description, array $lines): void
    {
        $accounting->post(self::JOURNAL_TYPE, $date, $lines, [
            'description' => $description,
            'created_at' => $date->copy()->setTime(10, 0),
        ]);
    }
}
