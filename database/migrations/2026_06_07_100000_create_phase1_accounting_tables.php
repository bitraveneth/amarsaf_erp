<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('period_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'period_number']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('entry_date');
            $table->string('journal_type', 50);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('posted');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accounting_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reversed_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('reversal_of_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['entry_date', 'status']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained();
            $table->unsignedSmallInteger('line_number')->default(1);
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->timestamps();

            $table->index('account_id');
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->after('journal_entry_id')->constrained()->nullOnDelete();
        });

        $this->seedDefaultFiscalYear();
        $this->migrateLegacyLedgerEntries();
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
            $table->dropConstrainedForeignId('journal_entry_id');
        });

        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('fiscal_years');
    }

    protected function seedDefaultFiscalYear(): void
    {
        $year = (int) now()->format('Y');
        $start = sprintf('%d-01-01', $year);
        $end = sprintf('%d-12-31', $year);

        $fiscalYearId = DB::table('fiscal_years')->insertGetId([
            'name' => (string) $year,
            'start_date' => $start,
            'end_date' => $end,
            'is_closed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($month = 1; $month <= 12; $month++) {
            $periodStart = sprintf('%d-%02d-01', $year, $month);
            $periodEnd = date('Y-m-t', strtotime($periodStart));

            DB::table('accounting_periods')->insert([
                'fiscal_year_id' => $fiscalYearId,
                'name' => date('F Y', strtotime($periodStart)),
                'period_number' => $month,
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'is_closed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function migrateLegacyLedgerEntries(): void
    {
        if (! Schema::hasTable('ledger_entries') || ! Schema::hasTable('accounts')) {
            return;
        }

        $accountsByName = DB::table('accounts')->pluck('id', 'name');

        $legacyEntries = DB::table('ledger_entries')->orderBy('id')->get();
        if ($legacyEntries->isEmpty()) {
            return;
        }

        $groups = $legacyEntries->groupBy(function ($entry) {
            return implode('|', [
                $entry->description ?? '',
                (string) ($entry->invoice_id ?? ''),
                (string) ($entry->order_id ?? ''),
                substr((string) $entry->created_at, 0, 19),
            ]);
        });

        foreach ($groups as $group) {
            $first = $group->first();
            $entryDate = substr((string) $first->created_at, 0, 10);
            $periodId = $this->resolvePeriodId($entryDate);

            $journalId = DB::table('journal_entries')->insertGetId([
                'number' => 'JE-MIG-' . str_pad((string) $first->id, 6, '0', STR_PAD_LEFT),
                'entry_date' => $entryDate,
                'journal_type' => 'legacy_migration',
                'source_type' => null,
                'source_id' => null,
                'description' => $first->description,
                'status' => 'posted',
                'posted_at' => $first->created_at,
                'posted_by' => null,
                'accounting_period_id' => $periodId,
                'order_id' => $first->order_id,
                'invoice_id' => $first->invoice_id,
                'created_at' => $first->created_at,
                'updated_at' => $first->updated_at,
            ]);

            $lineNumber = 1;
            foreach ($group as $entry) {
                $accountId = $accountsByName[$entry->account] ?? $this->createFallbackAccount($entry->account);

                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id' => $journalId,
                    'account_id' => $accountId,
                    'line_number' => $lineNumber++,
                    'description' => $entry->description,
                    'debit' => $entry->debit,
                    'credit' => $entry->credit,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                ]);

                DB::table('ledger_entries')->where('id', $entry->id)->update([
                    'journal_entry_id' => $journalId,
                    'account_id' => $accountId,
                ]);
            }
        }
    }

    protected function resolvePeriodId(string $entryDate): ?int
    {
        return DB::table('accounting_periods')
            ->whereDate('start_date', '<=', $entryDate)
            ->whereDate('end_date', '>=', $entryDate)
            ->value('id');
    }

    protected function createFallbackAccount(string $name): int
    {
        $code = '9' . str_pad((string) (DB::table('accounts')->count() + 1), 3, '0', STR_PAD_LEFT);

        return DB::table('accounts')->insertGetId([
            'code' => $code,
            'name' => $name,
            'type' => 'expense',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
