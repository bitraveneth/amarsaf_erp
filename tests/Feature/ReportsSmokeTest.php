<?php

namespace Tests\Feature;

use App\Support\ReportsCatalog;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ReportsSmokeTest extends TestCase
{
    public function test_reports_catalog_definitions_have_valid_route_names(): void
    {
        foreach (ReportsCatalog::definitions() as $report) {
            $routeName = $report['route'] ?? null;
            $this->assertNotEmpty($routeName, 'Report missing route: '.($report['id'] ?? 'unknown'));

            $this->assertTrue(
                Route::has($routeName),
                "Route [{$routeName}] for report [{$report['id']}] is not registered"
            );
        }
    }

    public function test_featured_reports_resolve_to_urls(): void
    {
        foreach (ReportsCatalog::featured(['range' => 'month']) as $report) {
            $this->assertNotEmpty($report['href'] ?? null);
            $this->assertStringContainsString('/admin/reports/', $report['href']);
        }
    }
}
