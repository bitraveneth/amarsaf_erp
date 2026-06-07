<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ExportDateRange;
use App\Support\ModuleExportRegistry;
use Illuminate\Http\Request;

class ExportCenterController extends Controller
{
    public function __invoke(Request $request)
    {
        $rangeMode = ExportDateRange::mode($request);
        $queryParams = ExportDateRange::queryParams($request);
        $groups = ModuleExportRegistry::modulesGroupedForUser($request->user(), $queryParams);
        $calendarDefaults = ExportDateRange::formDefaults($request);
        $quickFillPresets = collect(ExportDateRange::presets())
            ->keys()
            ->filter(fn (string $key) => $key !== 'all')
            ->mapWithKeys(fn (string $key) => [$key => ExportDateRange::quickFillDates($key)])
            ->all();

        return view('admin.exports.index', [
            'exportGroups' => $groups,
            'moduleCount' => collect($groups)->sum(fn (array $modules) => count($modules)),
            'rangePresets' => ExportDateRange::presets(),
            'selectedRange' => ExportDateRange::selectedKey($request),
            'rangeMode' => $rangeMode,
            'fromValue' => $request->query('from', $calendarDefaults['from']),
            'toValue' => $request->query('to', $calendarDefaults['to']),
            'periodLabel' => ExportDateRange::periodSummary($request),
            'rangeQuery' => $queryParams,
            'quickFillPresets' => $quickFillPresets,
            'featuredModules' => ModuleExportRegistry::featuredModulesForUser($request->user(), $queryParams),
        ]);
    }
}
