<?php

namespace Tests\Feature;

use App\Services\Accounting\IncomeStatementPresenter;
use App\Services\Accounting\PlDashboardPresenter;
use App\Services\Accounting\PlDriverLinkService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfitAndLossReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Profit and loss tests require pdo_sqlite.');
        }

        $this->bootInMemorySqlite();
        $this->createAccountingSchema();
    }

    protected function bootInMemorySqlite(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    protected function createAccountingSchema(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('type')->nullable();
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
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    protected function sampleStatement(float $netRevenue = 1000, float $cogs = 400, float $admin = 100, float $selling = 150, float $financial = 50): array
    {
        $grossProfit = $netRevenue - $cogs;
        $operatingProfit = $grossProfit - $admin - $selling;
        $netProfit = $grossProfit - $admin - $selling - $financial;

        return [
            'revenue' => ['rows' => collect()],
            'manufacturing' => ['rows' => collect()],
            'operating' => ['rows' => collect()],
            'netRevenue' => $netRevenue,
            'manufacturingCost' => $cogs,
            'grossProfit' => $grossProfit,
            'administrativeExpenses' => $admin,
            'sellingExpenses' => $selling,
            'financialExpenses' => $financial,
            'operatingExpenses' => $admin + $selling + $financial,
            'operatingProfit' => $operatingProfit,
            'netProfit' => $netProfit,
            'commissionExpense' => 30,
            'payrollExpense' => 80,
            'deliveryExpense' => 40,
        ];
    }

    public function test_income_statement_presenter_builds_milestone_rows(): void
    {
        $presenter = app(IncomeStatementPresenter::class);
        $from = Carbon::parse('2026-06-01');
        $to = Carbon::parse('2026-06-30');

        $result = $presenter->present(
            $this->sampleStatement(),
            'Jun 2026',
            'BDT',
            $from,
            $to
        );

        $milestones = collect($result['rows'])->where('type', 'milestone')->pluck('milestone_key')->all();

        $this->assertSame(['gross', 'operating', 'net'], $milestones);
        $this->assertSame('100.0%', collect($result['rows'])->firstWhere('type', 'section')['pct_display']);
    }

    public function test_income_statement_presenter_merges_prior_period_comparison(): void
    {
        $presenter = app(IncomeStatementPresenter::class);
        $from = Carbon::parse('2026-06-01');
        $to = Carbon::parse('2026-06-30');

        $current = $this->sampleStatement(1000, 400, 100, 150, 50);
        $prior = $this->sampleStatement(800, 320, 100, 150, 50);

        $result = $presenter->present(
            $current,
            'Jun 2026',
            'BDT',
            $from,
            $to,
            $prior,
            'May 2026'
        );

        $this->assertTrue($result['hasComparison']);

        $netRow = collect($result['rows'])->firstWhere('milestone_key', 'net');
        $this->assertSame('300.00', $netRow['amount_display']);
        $this->assertSame('180.00', $netRow['prior_display']);
        $this->assertSame('+120.00', $netRow['variance_display']);
        $this->assertSame('+66.7%', $netRow['variance_pct_display']);
    }

    public function test_pl_dashboard_presenter_builds_hero_and_waterfall(): void
    {
        $presenter = app(PlDashboardPresenter::class);
        $from = Carbon::parse('2026-06-01');
        $to = Carbon::parse('2026-06-30');
        $current = $this->sampleStatement();
        $prior = $this->sampleStatement(800, 320, 100, 150, 50);

        $result = $presenter->present($current, 'BDT', 'Jun 2026', $from, $to, $prior);

        $this->assertSame('300', $result['hero']['net_profit_display']);
        $this->assertSame(60.0, $result['hero']['gross_margin']);
        $this->assertNotNull($result['hero']['delta']);
        $this->assertCount(6, $result['waterfall']);
        $this->assertNotEmpty($result['drivers']);
    }

    public function test_driver_link_service_maps_known_slugs(): void
    {
        $service = app(PlDriverLinkService::class);
        $from = Carbon::parse('2026-06-01');
        $to = Carbon::parse('2026-06-30');

        $link = $service->accountLink('commission_expense', $from, $to);

        $this->assertNotNull($link);
        $this->assertStringContainsString('/admin/reports/commissions', $link['href']);
    }
}
