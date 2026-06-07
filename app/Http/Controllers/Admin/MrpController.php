<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MrpService;
use Illuminate\Http\Request;

class MrpController extends Controller
{
    public function index(MrpService $mrp)
    {
        return view('admin.mrp.index', [
            'reorderSuggestions' => $mrp->suggestions(),
            'bomSuggestions' => $mrp->materialRequirementsFromBom(),
        ]);
    }
}
