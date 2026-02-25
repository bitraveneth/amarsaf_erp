@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Menu manager</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage sidebar groups and items. This controls what appears on the left navigation.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            Back to dashboard
        </a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/40 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[320px,minmax(0,1fr)]">
        {{-- Groups column --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Groups</h2>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">High-level sections in the sidebar.</p>

            <form method="POST" action="{{ route('admin.menu.groups.store') }}" class="mb-4 flex gap-2">
                @csrf
                <input type="text" name="title" placeholder="e.g. Inventory" required
                    class="flex-1 rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <button type="submit" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    Add
                </button>
            </form>

            @if($groups->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400">No groups yet. Use the form above to add one.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach($groups as $group)
                        <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs
                                   hover:border-brand-500/70 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900/60 dark:hover:bg-gray-900 transition-colors">
                            <div>
                                <div class="text-[11px] font-semibold tracking-wide text-gray-900 dark:text-gray-100">
                                    {{ $group->title }}
                                </div>
                                <div class="mt-1 inline-flex items-center gap-1">
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $group->items->count() }} item{{ $group->items->count() === 1 ? '' : 's' }}
                                    </span>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        Position: {{ $group->position }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <form method="POST" action="{{ route('admin.menu.groups.move', $group) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="rounded border border-gray-200 bg-white px-1.5 py-1 text-[11px] text-gray-500 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900">
                                        ↑
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.menu.groups.move', $group) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="rounded border border-gray-200 bg-white px-1.5 py-1 text-[11px] text-gray-500 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900">
                                        ↓
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.menu.groups.delete', $group) }}" onsubmit="return confirm('Delete group {{ $group->title }} and all its items?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-error-200 bg-error-50 px-2 py-1 text-[11px] font-medium text-error-600 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Items column --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Items by group</h2>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Modules and links that appear under each group.</p>

            @if($groups->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400">Create a group first to start adding items.</p>
            @else
                {{-- Show groups as responsive masonry-style cards --}}
                <div class="columns-1 gap-4 md:columns-2 xl:columns-2 [column-fill:_balance]">
                    @foreach($groups as $group)
                        <div class="mb-4 break-inside-avoid rounded-xl border border-gray-800 bg-gray-900/70 shadow-theme-xs">
                            <div class="flex items-center justify-between border-b border-gray-800/80 bg-gray-900/80 px-3 py-2">
                                <h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-300">
                                    {{ $group->title }}
                                </h3>
                                <span class="text-[10px] text-gray-500">Group key: {{ $group->key }}</span>
                            </div>

                            {{-- Existing items --}}
                            @if($group->items->isEmpty())
                                <p class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400">No items yet.</p>
                            @else
                                <div class="mb-3 space-y-2 px-3 py-3">
                                    @foreach($group->items as $item)
                                        <div class="rounded-lg bg-gray-900 px-3 py-2 text-xs text-gray-100 shadow-sm">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold">{{ $item->name }}</span>
                                                    @if($item->icon)
                                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-gray-800 dark:text-gray-300">icon: {{ $item->icon }}</span>
                                                    @endif
                                                    @if($item->permission)
                                                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">{{ $item->permission }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <form method="POST" action="{{ route('admin.menu.items.move', $item) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" class="rounded border border-gray-600 bg-gray-800 px-1.5 py-0.5 text-[10px] text-gray-300 hover:border-brand-400 hover:text-brand-300">
                                                            ↑
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.menu.items.move', $item) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" class="rounded border border-gray-600 bg-gray-800 px-1.5 py-0.5 text-[10px] text-gray-300 hover:border-brand-400 hover:text-brand-300">
                                                            ↓
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.menu.items.delete', $item) }}" onsubmit="return confirm('Delete item {{ $item->name }} and its sub-items?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-[10px] text-gray-500 hover:text-error-400 hover:underline">
                                                            Delete
                                                        </button>
                                                    </form>
                                                    {{-- Move to another group --}}
                                                    <form method="POST" action="{{ route('admin.menu.items.move-group', $item) }}" class="ml-1">
                                                        @csrf
                                                        <select name="menu_group_id" class="rounded border border-gray-700 bg-gray-900 px-1 py-0.5 text-[10px] text-gray-300 focus:border-brand-400 focus:outline-none focus:ring-0">
                                                            @foreach($groups as $targetGroup)
                                                                <option value="{{ $targetGroup->id }}" @selected($targetGroup->id === $group->id)>
                                                                    {{ $targetGroup->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="ml-1 rounded border border-gray-600 bg-gray-800 px-1.5 py-0.5 text-[10px] text-gray-300 hover:border-brand-400 hover:text-brand-300">
                                                            Move
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="mt-1 text-[11px] text-gray-400">Path: {{ $item->path }}</div>

                                            @if($item->children->isNotEmpty())
                                                <div class="mt-2 grid gap-1 border-t border-dashed border-gray-200 pt-2 text-[11px] dark:border-gray-700">
                                                    @foreach($item->children as $child)
                                                        <div class="flex flex-col gap-1 rounded-md bg-gray-900/60 px-2 py-1.5">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span class="font-medium">{{ $child->name }}</span>
                                                                    <span class="ml-1 text-gray-500">{{ $child->path }}</span>
                                                                </div>
                                                                <form method="POST" action="{{ route('admin.menu.items.delete', $child) }}" onsubmit="return confirm('Delete link {{ $child->name }}?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-[11px] text-error-500 hover:text-error-600">Remove</button>
                                                                </form>
                                                            </div>
                                                            <form method="POST" action="{{ route('admin.menu.items.update', $child) }}" class="mt-1 flex flex-wrap items-center gap-2">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input
                                                                    type="text"
                                                                    name="name"
                                                                    value="{{ $child->name }}"
                                                                    class="w-32 flex-1 rounded border border-gray-200 bg-transparent px-2 py-1 text-[11px] text-gray-100 placeholder:text-gray-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                                />
                                                                <input
                                                                    type="text"
                                                                    name="path"
                                                                    value="{{ $child->path }}"
                                                                    class="w-40 flex-1 rounded border border-gray-200 bg-transparent px-2 py-1 text-[11px] text-gray-100 placeholder:text-gray-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                                />
                                                                @if(isset($permissions) && $permissions->isNotEmpty())
                                                                    <select name="permission" class="w-40 rounded border border-gray-200 bg-transparent px-2 py-1 text-[11px] text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                                        <option value="">No key (inherits group access)</option>
                                                                        @foreach($permissions as $perm)
                                                                            <option value="{{ $perm->name }}" @selected($child->permission === $perm->name)>
                                                                                {{ $perm->name }} — {{ $perm->label }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                                <button type="submit" class="rounded bg-gray-800 px-2.5 py-1 text-[10px] font-semibold text-gray-100 hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                                                    Save
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Add sub-item (direct child of the top-level item) --}}
                                            <form method="POST" action="{{ route('admin.menu.items.store') }}" class="mt-2 flex flex-wrap items-end gap-2 border-t border-dashed border-gray-200 pt-2 dark:border-gray-700">
                                                @csrf
                                                <input type="hidden" name="menu_group_id" value="{{ $group->id }}">
                                                <input type="hidden" name="parent_id" value="{{ $item->id }}">
                                                <input type="text" name="name" placeholder="Sub-item label" required class="w-40 flex-1 rounded border border-gray-200 bg-transparent px-2 py-1.5 text-[11px] text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                                <input type="text" name="path" placeholder="/admin/..." class="w-40 flex-1 rounded border border-gray-200 bg-transparent px-2 py-1.5 text-[11px] text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                                @if(isset($permissions) && $permissions->isNotEmpty())
                                                    <select name="permission" class="w-40 rounded border border-gray-200 bg-transparent px-2 py-1.5 text-[11px] text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                        <option value="">No permission (visible to allowed roles)</option>
                                                        @foreach($permissions as $perm)
                                                            <option value="{{ $perm->name }}">
                                                                {{ $perm->name }} — {{ $perm->label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                <button type="submit" class="rounded bg-gray-900 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                                    Add link
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
