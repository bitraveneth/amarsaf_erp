<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, GlobalSearchService $search)
    {
        $query = trim((string) $request->query('q', ''));
        $results = $query !== '' ? $search->search($query, 25) : collect();

        return view('admin.search.index', compact('query', 'results'));
    }

    public function suggest(Request $request, GlobalSearchService $search)
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'data' => $search->search($query, 10)->values(),
        ]);
    }
}
