<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission;
use App\Http\Controllers\Controller;
use App\Support\ModuleExportRegistry;
use App\Support\TabularExport;
use Illuminate\Http\Request;

class ModuleExportController extends Controller
{
    public function __invoke(Request $request, string $module, string $format)
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $export = ModuleExportRegistry::resolve($module, $request);

        if (! Permission::can($request->user(), $export['permission'])) {
            abort(403);
        }

        return TabularExport::download(
            $format,
            $export['title'],
            $export['filename'],
            $export['columns'],
            $export['rows']
        );
    }
}
