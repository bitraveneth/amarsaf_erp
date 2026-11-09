<?php

namespace App\Services\Accounting;

use App\Models\Expense;
use App\Models\FleetExpense;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LogisticsBill;
use App\Models\PurchaseBill;
use App\Models\SalaryDistribution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class LedgerGapReport
{
    /**
     * Documents that should be on the ledger and have no posted voucher.
     *
     * @return list<array<string, mixed>>
     */
    public function sections(): array
    {
        if (! Schema::hasTable('journal_entries')) {
            return [];
        }

        return array_values(array_filter([
            $this->expenses(),
            $this->purchaseBills(),
            $this->invoices(),
            $this->payroll(),
            $this->fleetExpenses(),
            $this->logisticsBills(),
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function expenses(): ?array
    {
        if (! Schema::hasTable('expenses')) {
            return null;
        }

        $query = $this->withoutPostedJournal(Expense::query(), Expense::class, 'expenses.id')
            ->where('amount', '>', 0)
            ->orderByDesc('date')
            ->orderByDesc('id');

        return $this->section(
            'Expenses',
            'Saved costs with no posted voucher. Sync posts the ones the open month and accounts allow.',
            route('admin.expenses.index'),
            $query,
            'expenses.amount',
            function (Expense $expense): array {
                $when = $expense->date ? Carbon::parse($expense->date)->format('d M Y') : 'No date';

                return [
                    'label' => $expense->description ?: ($expense->category ?: 'Expense'),
                    'meta' => $when,
                    'amount' => (float) $expense->amount,
                    'reason' => $this->reason($expense->journal_status ?? null),
                    'href' => route('admin.expenses.edit', $expense),
                ];
            },
            true
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function purchaseBills(): ?array
    {
        if (! Schema::hasTable('purchase_bills')) {
            return null;
        }

        $query = $this->withoutPostedJournal(PurchaseBill::query()->with('supplier'), PurchaseBill::class, 'purchase_bills.id')
            ->orderByDesc('bill_date')
            ->orderByDesc('id');

        return $this->section(
            'Purchase bills',
            'Supplier bills that never posted to accounts payable.',
            route('admin.bills.index'),
            $query,
            'purchase_bills.net_total + purchase_bills.vat_amount',
            function (PurchaseBill $bill): array {
                return [
                    'label' => $bill->number ?: 'Bill '.$bill->id,
                    'meta' => trim(($bill->supplier?->name ?: 'Supplier').' · '.($bill->bill_date?->format('d M Y') ?: 'No date')),
                    'amount' => (float) $bill->net_total + (float) $bill->vat_amount,
                    'reason' => $this->reason($bill->journal_status ?? null),
                    'href' => route('admin.bills.show', $bill),
                ];
            }
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function invoices(): ?array
    {
        if (! Schema::hasTable('invoices')) {
            return null;
        }

        $query = $this->withoutPostedJournal(Invoice::query(), Invoice::class, 'invoices.id')
            ->orderByDesc('issued_at')
            ->orderByDesc('id');

        return $this->section(
            'Customer invoices',
            'Invoices that never posted to trade debtors.',
            route('admin.finance.index'),
            $query,
            'invoices.net_total + invoices.vat_amount',
            function (Invoice $invoice): array {
                return [
                    'label' => $invoice->number ?: 'Invoice '.$invoice->id,
                    'meta' => $invoice->issued_at?->format('d M Y') ?: 'No date',
                    'amount' => (float) $invoice->net_total + (float) $invoice->vat_amount,
                    'reason' => $this->reason($invoice->journal_status ?? null),
                    'href' => route('admin.finance.show', $invoice),
                ];
            }
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function payroll(): ?array
    {
        if (! Schema::hasTable('salary_distributions')) {
            return null;
        }

        $query = $this->withoutPostedJournal(SalaryDistribution::query()->with('employee'), SalaryDistribution::class, 'salary_distributions.id')
            ->orderByDesc('period_end')
            ->orderByDesc('id');

        return $this->section(
            'Payroll',
            'Salary rows with no posted payroll voucher.',
            route('admin.salary-distributions.index'),
            $query,
            'salary_distributions.base_salary + salary_distributions.bonus + salary_distributions.ta_allowances + salary_distributions.da_allowances + salary_distributions.commission + salary_distributions.overtime_pay',
            function (SalaryDistribution $row): array {
                $amount = (float) $row->base_salary
                    + (float) $row->bonus
                    + (float) $row->ta_allowances
                    + (float) $row->da_allowances
                    + (float) $row->commission
                    + (float) $row->overtime_pay;
                $period = ($row->period_start?->format('d M Y') ?: '—').' – '.($row->period_end?->format('d M Y') ?: '—');

                return [
                    'label' => $row->employee?->name ?: 'Employee',
                    'meta' => $period,
                    'amount' => $amount,
                    'reason' => $this->reason($row->journal_status ?? null),
                    'href' => route('admin.salary-distributions.edit', $row),
                ];
            }
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fleetExpenses(): ?array
    {
        if (! Schema::hasTable('fleet_expenses')) {
            return null;
        }

        $query = $this->withoutPostedJournal(FleetExpense::query(), FleetExpense::class, 'fleet_expenses.id')
            ->where('amount', '>', 0)
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        return $this->section(
            'Fleet expenses',
            'Vehicle costs saved without a posted voucher.',
            route('admin.fleet-expenses.index'),
            $query,
            'fleet_expenses.amount',
            function (FleetExpense $expense): array {
                return [
                    'label' => $expense->description ?: ucfirst(str_replace('_', ' ', (string) $expense->expense_type)),
                    'meta' => $expense->expense_date ? Carbon::parse($expense->expense_date)->format('d M Y') : 'No date',
                    'amount' => (float) $expense->amount,
                    'reason' => $this->reason($expense->journal_status ?? null),
                    'href' => route('admin.fleet-expenses.edit', $expense),
                ];
            }
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function logisticsBills(): ?array
    {
        if (! Schema::hasTable('logistics_bills')) {
            return null;
        }

        $query = $this->withoutPostedJournal(LogisticsBill::query(), LogisticsBill::class, 'logistics_bills.id')
            ->orderByDesc('bill_date')
            ->orderByDesc('id');

        return $this->section(
            'Logistics bills',
            'Carrier bills that never posted to the ledger.',
            route('admin.logistics-bills.index'),
            $query,
            'logistics_bills.net_total + logistics_bills.vat_amount',
            function (LogisticsBill $bill): array {
                return [
                    'label' => $bill->number ?: 'Bill '.$bill->id,
                    'meta' => $bill->bill_date?->format('d M Y') ?: 'No date',
                    'amount' => (float) $bill->net_total + (float) $bill->vat_amount,
                    'reason' => $this->reason($bill->journal_status ?? null),
                    'href' => route('admin.logistics-bills.show', $bill),
                ];
            }
        );
    }

    protected function withoutPostedJournal(Builder $query, string $sourceType, string $idColumn): Builder
    {
        return $query->whereNotExists(function ($journal) use ($sourceType, $idColumn) {
            $journal->selectRaw('1')
                ->from('journal_entries')
                ->whereColumn('journal_entries.source_id', $idColumn)
                ->where('journal_entries.source_type', $sourceType)
                ->where('journal_entries.status', 'posted');
        });
    }

    protected function withJournalStatus(Builder $query, string $sourceType, string $idColumn): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->select($table.'.*')
            ->selectSub(
                JournalEntry::query()
                    ->select('status')
                    ->whereColumn('journal_entries.source_id', $idColumn)
                    ->where('journal_entries.source_type', $sourceType)
                    ->latest('id')
                    ->limit(1),
                'journal_status'
            );
    }

    /**
     * @param  callable(mixed): array<string, mixed>  $map
     * @return array<string, mixed>
     */
    protected function section(string $title, string $description, string $listUrl, Builder $query, string $amountSql, callable $map, bool $canSync = false): array
    {
        $count = (clone $query)->count();
        $amount = $this->sumAmount(clone $query, $amountSql);
        $idColumn = $query->getModel()->getTable().'.id';
        $sourceType = $query->getModel()::class;
        $rows = $this->withJournalStatus(clone $query, $sourceType, $idColumn)->limit(40)->get()->map($map)->all();

        return [
            'title' => $title,
            'description' => $description,
            'list_url' => $listUrl,
            'can_sync' => $canSync && $count > 0,
            'count' => $count,
            'amount' => $amount,
            'rows' => $rows,
        ];
    }

    protected function sumAmount(Builder $query, string $amountSql): float
    {
        $query->getQuery()->columns = null;
        $query->getQuery()->orders = null;
        $query->getQuery()->limit = null;

        return (float) $query->selectRaw('COALESCE(SUM('.$amountSql.'), 0) as gap_amount')->value('gap_amount');
    }

    protected function reason(?string $status): string
    {
        return match ($status) {
            'draft' => 'Draft voucher',
            'reversed' => 'Voucher reversed',
            default => 'Never posted',
        };
    }
}
