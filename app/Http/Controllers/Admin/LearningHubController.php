<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Learning\LearningHubRepository;
use Illuminate\Http\Request;

class LearningHubController extends Controller
{
    public function __invoke(Request $request)
    {
        $modules = LearningHubRepository::modules();
        $ui = LearningHubRepository::ui();
        $roles = LearningHubRepository::roles();
        $initialModule = $request->query('module');

        if ($initialModule !== null && ! collect($modules)->contains(fn ($m) => ($m['slug'] ?? '') === $initialModule)) {
            $initialModule = null;
        }

        $user = $request->user();
        $defaultRole = LearningHubRepository::defaultRoleFilter($user?->role);

        return view('admin.learning.index', [
            'modules' => $modules,
            'ui' => $ui,
            'roles' => $roles,
            'initialModule' => $initialModule,
            'defaultRole' => $defaultRole,
            'learningPath' => LearningHubRepository::learningPath($defaultRole !== 'all' ? $defaultRole : $user?->role),
        ]);
    }
}
