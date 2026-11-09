<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Account;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Accounting\AccountResolver;
use App\Services\Accounting\UnpostedExpenseNotice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UnpostedExpenseNoticeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Expense notice tests require pdo_sqlite.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        app(AccountResolver::class)->forget();

        $this->withoutMiddleware([
            Authenticate::class,
            CheckPermission::class,
            VerifyCsrfToken::class,
        ]);
    }

    public function test_a_saved_expense_without_a_voucher_explains_why_it_is_missing_from_the_statement(): void
    {
        $this->account('marketing_expense', 'Marketing expense', 'expense');
        $this->account('bank_brac', 'BRAC Bank', 'asset');

        Expense::create([
            'date' => '2026-11-02',
            'category' => 'Marketing',
            'description' => 'Facebook ads',
            'amount' => 30000,
            'status' => 'recorded',
            'payment_type' => 'bank',
            'payment_account_key' => 'bank_default',
        ]);

        $posted = Expense::create([
            'date' => '2026-11-01',
            'category' => 'Utilities',
            'description' => 'Already in the books',
            'amount' => 500,
            'status' => 'recorded',
        ]);
        JournalEntry::create([
            'number' => 'JE-1',
            'entry_date' => '2026-11-01',
            'journal_type' => 'expense',
            'source_type' => Expense::class,
            'source_id' => $posted->id,
            'status' => 'posted',
        ]);

        $reversed = Expense::create([
            'date' => '2026-10-15',
            'category' => 'Travel & TA',
            'description' => 'Field visit',
            'amount' => 800,
            'status' => 'recorded',
        ]);
        JournalEntry::create([
            'number' => 'JE-2',
            'entry_date' => '2026-10-15',
            'journal_type' => 'expense',
            'source_type' => Expense::class,
            'source_id' => $reversed->id,
            'status' => 'reversed',
        ]);

        $notice = app(UnpostedExpenseNotice::class)->summary();

        $this->assertNotNull($notice);
        $this->assertSame(2, $notice['count']);
        $this->assertStringContainsString('without a journal voucher', $notice['why']);
        $this->assertStringContainsString('reversed', $notice['why']);
        $this->assertTrue($notice['can_sync']);

        $this->actingAs(User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'secret',
            'role' => 'super_admin',
        ]));

        $html = view('components.layout.ledger-gap-banner', ['notice' => $notice])->render();
        $this->assertStringContainsString('not in the profit and loss ledger', $html);
        $this->assertStringContainsString('Review expenses', $html);
        $this->assertStringContainsString(route('admin.expenses.index'), $html);
        $this->assertStringNotContainsString('Facebook ads', $html);
        $this->assertStringNotContainsString('name="scope" value="missing"', $html);
    }

    public function test_sales_and_warehouse_staff_do_not_see_the_ledger_gap_banner(): void
    {
        $this->account('marketing_expense', 'Marketing expense', 'expense');
        $this->account('bank_brac', 'BRAC Bank', 'asset');

        Expense::create([
            'date' => '2026-11-02',
            'category' => 'Marketing',
            'description' => 'Facebook ads',
            'amount' => 30000,
            'status' => 'recorded',
            'payment_type' => 'bank',
            'payment_account_key' => 'bank_default',
        ]);

        $notice = app(UnpostedExpenseNotice::class)->summary();

        foreach (['admin', 'accounts_officer'] as $role) {
            $this->actingAs(User::create([
                'name' => $role,
                'email' => $role.'@example.com',
                'password' => 'secret',
                'role' => $role,
            ]));

            $html = view('components.layout.ledger-gap-banner', ['notice' => $notice])->render();

            $this->assertStringContainsString('not in the profit and loss ledger', $html);
        }

        foreach (['sales_officer', 'warehouse_officer', 'purchase_executive', 'production_officer'] as $role) {
            $this->actingAs(User::create([
                'name' => $role,
                'email' => $role.'@example.com',
                'password' => 'secret',
                'role' => $role,
            ]));

            $html = view('components.layout.ledger-gap-banner', ['notice' => $notice])->render();

            $this->assertStringNotContainsString('not in the profit and loss ledger', $html);
            $this->assertStringNotContainsString('Sync missing expenses', $html);
        }
    }

    public function test_sync_posts_the_missing_expense_and_the_banner_goes_away(): void
    {
        $this->account('marketing_expense', 'Marketing expense', 'expense');
        $this->account('bank_brac', 'BRAC Bank', 'asset');

        Expense::create([
            'date' => '2026-11-02',
            'category' => 'Marketing',
            'description' => 'Facebook ads',
            'amount' => 1200,
            'status' => 'recorded',
            'payment_type' => 'bank',
            'payment_account_key' => 'bank_default',
        ]);

        $this->assertNotNull(app(UnpostedExpenseNotice::class)->summary());

        $response = $this->post(route('admin.expenses.sync-ledger'), [
            'scope' => 'missing',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', '1 expense(s) posted to the ledger.');
        $this->assertSame(1, JournalEntry::query()->where('status', 'posted')->where('journal_type', 'expense')->count());
        $this->assertNull(app(UnpostedExpenseNotice::class)->summary());
    }

    protected function account(string $slug, string $name, string $type): void
    {
        Account::create([
            'code' => $slug,
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'is_group' => false,
            'is_active' => true,
        ]);
    }

    protected function createSchema(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_group')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payment_type')->nullable();
            $table->string('payment_account_key')->nullable();
            $table->string('analytic_label')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('payable_account_id')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->date('entry_date');
            $table->string('journal_type')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('posted');
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->unsignedBigInteger('accounting_period_id')->nullable();
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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('url')->nullable();
            $table->string('secret')->nullable();
            $table->text('events')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
}
