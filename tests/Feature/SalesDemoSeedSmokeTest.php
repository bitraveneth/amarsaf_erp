<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ProductionRun;
use App\Models\SalaryDistribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDemoSeedSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'mysql') === 'sqlite') {
            $this->markTestSkipped('Sales demo seed smoke test requires MySQL (migrations use MySQL-specific syntax).');
        }

        parent::setUp();
    }

    public function test_sales_demo_seed_populates_core_modules(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(5, Agent::count());
        $this->assertGreaterThanOrEqual(100, Order::count());
        $this->assertGreaterThanOrEqual(80, Invoice::count());
        $this->assertGreaterThanOrEqual(50, Delivery::count());
        $this->assertGreaterThanOrEqual(80, ProductionRun::count());
        $this->assertGreaterThanOrEqual(50, Expense::count());
        $this->assertGreaterThanOrEqual(100, SalaryDistribution::count());
    }
}
