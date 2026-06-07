<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Assistant\AssistantIntentRouter;
use App\Services\Assistant\AssistantResponseComposer;
use Illuminate\Http\Request;

class ErpAssistantController extends Controller
{
    public function bootstrap(AssistantResponseComposer $composer)
    {
        return response()->json($composer->bootstrap(auth()->user()));
    }

    public function ask(Request $request, AssistantIntentRouter $router, AssistantResponseComposer $composer)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = trim($validated['message']);
        $route = $router->route($message);
        $response = $composer->compose(auth()->user(), $route, $message);

        return response()->json($response);
    }
}
