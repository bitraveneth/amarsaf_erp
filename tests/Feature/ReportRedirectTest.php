<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ReportRedirectTest extends TestCase
{
    /** @return array<string, string> */
    protected function legacyRedirects(): array
    {
        return [
            'admin/reports/pl' => 'admin/reports/income-statement',
            'admin/reports/bs' => 'admin/reports/balance-sheet',
            'admin/reports/cashflow' => 'admin/reports/cash-flow',
            'admin/reports/agents' => 'admin/reports/agent-performance',
            'admin/reports/production' => 'admin/reports/production-summary',
            'admin/reports/payroll' => 'admin/reports/payroll-summary',
            'admin/reports/vat' => 'admin/reports/vat-report',
        ];
    }

    public function test_legacy_report_routes_are_registered(): void
    {
        foreach ($this->legacyRedirects() as $legacyUri => $targetUri) {
            $legacyRoute = collect(Route::getRoutes())->first(
                fn ($route) => $route->uri() === $legacyUri && in_array('GET', $route->methods(), true)
            );

            $this->assertNotNull($legacyRoute, "Missing legacy route: {$legacyUri}");

            $targetRoute = collect(Route::getRoutes())->first(
                fn ($route) => $route->uri() === $targetUri && in_array('GET', $route->methods(), true)
            );

            $this->assertNotNull($targetRoute, "Missing canonical route: {$targetUri}");
        }
    }

    public function test_canonical_report_route_names_resolve_to_descriptive_uris(): void
    {
        $this->assertSame('/admin/reports/income-statement', route('admin.reports.pl', [], false));
        $this->assertSame('/admin/reports/balance-sheet', route('admin.reports.bs', [], false));
        $this->assertSame('/admin/reports/cash-flow', route('admin.reports.cashflow', [], false));
        $this->assertSame('/admin/reports/agent-performance', route('admin.reports.agents', [], false));
        $this->assertSame('/admin/reports/production-summary', route('admin.reports.production', [], false));
        $this->assertSame('/admin/reports/payroll-summary', route('admin.reports.payroll', [], false));
        $this->assertSame('/admin/reports/vat-report', route('admin.reports.vat', [], false));
    }
}
