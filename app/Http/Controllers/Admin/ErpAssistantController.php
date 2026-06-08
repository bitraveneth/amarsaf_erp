<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Assistant\AssistantIntentRouter;
use App\Services\Assistant\AssistantResponseComposer;
use App\Services\Assistant\LearningAssistantService;
use Illuminate\Http\Request;

class ErpAssistantController extends Controller
{
    public function bootstrap(Request $request, AssistantResponseComposer $composer)
    {
        $context = LearningAssistantService::contextFromRequest($request);

        return response()->json($composer->bootstrap($request->user(), $context));
    }

    public function ask(Request $request, AssistantIntentRouter $router, AssistantResponseComposer $composer, LearningAssistantService $learning)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'context' => ['sometimes', 'array'],
            'context.source' => ['sometimes', 'string', 'max:40'],
            'context.module_slug' => ['sometimes', 'nullable', 'string', 'max:80'],
            'context.lesson_index' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'context.locale' => ['sometimes', 'string', 'in:en,bn'],
            'context.role' => ['sometimes', 'nullable', 'string', 'max:80'],
        ]);

        $message = trim($validated['message']);
        $context = LearningAssistantService::contextFromRequest($request);
        $user = $request->user();

        if (AssistantIntentRouter::isBusinessQuestion($message)) {
            $route = $router->route($message);

            return response()->json($composer->compose($user, $route, $message));
        }

        $learningReply = $learning->tryAnswer($user, $message, $context);
        if ($learningReply !== null) {
            return response()->json($learningReply);
        }

        $route = $router->route($message);

        return response()->json($composer->compose($user, $route, $message));
    }
}
