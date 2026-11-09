<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use App\Services\Accounting\ChartOfAccountsSummaryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Proves journal reverse + COA Balances behavior.
 * Expected after reverse: cash back to pre-journal amount (0 here).
 */
class JournalReversalBalanceTest extends TestCase
{
    protected Account $cash;

    protected Account $equity;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Requires pdo_sqlite.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();

        $this->cash = Account::create([
            'code' => '1111',
            'name' => 'Cash in Hand',
            'slug' => 'cash_in_hand',
            'type' => 'asset',
            'is_group' => false,
            'is_active' => true,
            'level' => 1,
            'sort_order' => 1,
        ]);

        $this->equity = Account::create([
            'code' => '3000',
            'name' => 'Owner Equity',
            'slug' => 'owner_equity',
            'type' => 'equity',
            'is_group' => false,
            'is_active' => true,
            'level' => 1,
            'sort_order' => 1,
        ]);
    }

    public function test_coa_balance_returns_to_zero_after_journal_is_reversed(): void
    {
        $accounting = app(AccountingService::class);
        $asOf = Carbon::parse('2026-11-09');

        $journal = $accounting->post('manual', $asOf, [
            ['account_id' => $this->cash->id, 'debit' => 50000, 'credit' => 0],
            ['account_id' => $this->equity->id, 'debit' => 0, 'credit' => 50000],
        ], [
            'description' => 'Test cash increase',
        ]);

        $this->assertSame(50000.0, $this->cashBalance($asOf), 'After post, cash should be +50,000');

        $accounting->reverse($journal, 'Undo test cash increase');

        $journal->refresh();
        $this->assertSame('reversed', $journal->status);
        $this->assertSame(1, JournalEntry::query()->where('status', 'posted')->count(), 'One posted reversal voucher');
        $this->assertSame(1, JournalEntry::query()->where('status', 'reversed')->count(), 'Original marked reversed');

        $this->assertSame(
            0.0,
            $this->cashBalance($asOf),
            'After reverse, cash must return to 0 (original + reversal should net out)'
        );
    }

    protected function cashBalance(Carbon $asOf): float
    {
        $summary = app(ChartOfAccountsSummaryService::class);
        $all = Account::query()->orderBy('id')->get();
        $data = $summary->summarize($asOf->copy()->startOfMonth(), $asOf, $all);

        return $summary->rollupBalance($this->cash, $data['period'], $data['closing']);
    }

    protected function createSchema(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_group')->default(false);
            $table->unsignedTinyInteger('level')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('path')->nullable();
            $table->string('report_root')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('entry_date');
            $table->string('journal_type', 50)->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('posted');
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->unsignedBigInteger('accounting_period_id')->nullable();
            $table->unsignedBigInteger('reversed_entry_id')->nullable();
            $table->unsignedBigInteger('reversal_of_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedSmallInteger('line_number')->default(1);
            $table->text('description')->nullable();
            $table->string('analytic_label')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('url')->nullable();
            $table->string('secret')->nullable();
            $table->json('events')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
}
