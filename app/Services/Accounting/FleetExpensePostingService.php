<?php

namespace App\Services\Accounting;

use App\Models\ExpenseCategory;
use App\Models\FleetExpense;
use Illuminate\Support\Carbon;

class FleetExpensePostingService
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function sync(FleetExpense $expense): void
    {
        $this->accounting->deleteByJournalTypeAndSource('fleet_expense', FleetExpense::class, $expense->id);

        if ((float) $expense->amount <= 0) {
            return;
        }

        if (! in_array($expense->status, [FleetExpense::STATUS_RECORDED, FleetExpense::STATUS_REVIEWED], true)) {
            return;
        }

        $analytic = $expense->analytic_label ?: ('vehicle:' . $expense->vehicle_id);
        $description = $expense->description ?: ($expense->typeLabel() . ' — ' . ($expense->vehicle?->name ?? 'Vehicle'));

        $this->accounting->post(
            'fleet_expense',
            Carbon::parse($expense->expense_date),
            [
                [
                    'account_key' => $this->expenseAccountKey($expense),
                    'debit' => $expense->amount,
                    'credit' => 0,
                    'description' => $description,
                    'analytic_label' => $analytic,
                ],
                [
                    'account_key' => $this->creditAccountKey($expense),
                    'debit' => 0,
                    'credit' => $expense->amount,
                    'description' => $description,
                    'analytic_label' => $analytic,
                ],
            ],
            [
                'description' => 'Fleet expense #' . $expense->id,
                'source_type' => FleetExpense::class,
                'source_id' => $expense->id,
            ]
        );
    }

    protected function expenseAccountKey(FleetExpense $expense): string
    {
        return match ($expense->expense_type) {
            FleetExpense::TYPE_FUEL => 'vehicle_fuel',
            FleetExpense::TYPE_MAINTENANCE => 'delivery_expense',
            FleetExpense::TYPE_RENT => 'delivery_expense',
            FleetExpense::TYPE_TOLL => 'delivery_expense',
            default => 'delivery_expense',
        };
    }

    protected function creditAccountKey(FleetExpense $expense): string
    {
        $paymentType = $expense->payment_type ?: ExpenseCategory::PAYMENT_BANK;

        return match ($paymentType) {
            ExpenseCategory::PAYMENT_PAYABLE => 'trade_creditors',
            ExpenseCategory::PAYMENT_CASH => $expense->payment_account_key ?: 'cash_in_hand',
            default => $expense->payment_account_key ?: 'bank_default',
        };
    }
}
