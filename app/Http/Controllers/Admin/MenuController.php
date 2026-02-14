<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\Permission;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $this->ensureSuperAdmin();

        $groups = MenuGroup::with(['items.children'])
            ->orderBy('position')
            ->get();

        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return view('admin.menu.index', compact('groups', 'permissions'));
    }

    public function storeGroup(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $position = (MenuGroup::max('position') ?? 0) + 1;

        MenuGroup::create([
            'title'     => $data['title'],
            'key'       => \Str::slug($data['title']),
            'position'  => $position,
            'is_active' => true,
        ]);

        return redirect()->route('admin.menu.index')->with('status', 'Group created.');
    }

    public function deleteGroup(MenuGroup $group)
    {
        $this->ensureSuperAdmin();

        $group->delete();

        return redirect()->route('admin.menu.index')->with('status', 'Group deleted.');
    }

    public function storeItem(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'menu_group_id' => 'required|exists:menu_groups,id',
            'parent_id'     => 'nullable|exists:menu_items,id',
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:50',
            'path'          => 'nullable|string|max:255',
            'permission'    => 'nullable|string|max:100',
        ]);

        $position = (MenuItem::where('menu_group_id', $data['menu_group_id'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position') ?? 0) + 1;

        MenuItem::create([
            'menu_group_id' => $data['menu_group_id'],
            'parent_id'     => $data['parent_id'] ?? null,
            'name'          => $data['name'],
            'key'           => $data['name'],
            'icon'          => $data['icon'] ?? null,
            'path'          => $data['path'] ?? '#',
            'permission'    => $data['permission'] ?? null,
            'position'      => $position,
            'is_active'     => true,
        ]);

        return redirect()->route('admin.menu.index')->with('status', 'Menu item added.');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'permission' => 'nullable|string|max:100',
        ]);

        $item->permission = $data['permission'] ?? null;
        $item->save();

        return redirect()->route('admin.menu.index')->with('status', 'Menu item updated.');
    }

    public function deleteItem(MenuItem $item)
    {
        $this->ensureSuperAdmin();

        $item->delete();

        return redirect()->route('admin.menu.index')->with('status', 'Menu item deleted.');
    }

    protected function ensureSuperAdmin(): void
    {
        $role = auth()->user()->role ?? null;
        if ($role !== 'super_admin') {
            abort(403, 'Only super admin can manage menu.');
        }
    }
}
