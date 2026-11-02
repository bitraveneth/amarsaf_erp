<?php

namespace Tests\Unit;

use App\Services\Accounting\FinancialReportExportService;
use App\Support\ExportDateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class ExportDateRangeCarbonTest extends TestCase
{
    public function test_custom_report_dates_match_the_finance_export_return_type(): void
    {
        $request = Request::create('/admin/exports/profit-loss', 'GET', [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]);

        $resolved = ExportDateRange::resolve($request);

        $this->assertInstanceOf(Carbon::class, $resolved['from']);
        $this->assertInstanceOf(Carbon::class, $resolved['to']);

        $method = new ReflectionMethod(FinancialReportExportService::class, 'resolveAsOf');
        $method->setAccessible(true);
        $asOf = $method->invoke(app(FinancialReportExportService::class), $request);

        $this->assertInstanceOf(Carbon::class, $asOf);
        $this->assertSame('2026-01-31', $asOf->toDateString());
    }
}
