@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showCreatePermission: false }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Permission manager
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Super admin can define permissions and assign them to roles.
            </p>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/40 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        {{-- Add permission (modal trigger) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:col-span-2 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Permissions</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage permission keys and labels used in the matrix below.</p>
            </div>
            <button
                type="button"
                @click="showCreatePermission = true"
                class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
            >
                Add permission
            </button>
        </div>

        {{-- Selected role permissions (grouped editor) --}}
        @if(!empty($selectedRoleKey) && $selectedRoleKey !== 'super_admin')
                <div class="mt-5 rounded-2xl border border-gray-200 bg-white p-4 text-xs text-gray-600 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Edit permissions for {{ $selectedRoleLabel ?? $selectedRoleKey }}
                        </h2>
                        <a href="{{ route('admin.permissions.index') }}" class="text-[11px] text-gray-500 hover:text-gray-300">
                            Clear
                        </a>
                    </div>
                    <form method="POST" action="{{ route('admin.permissions.roles.update-single', $selectedRoleKey) }}" class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @csrf
                        @foreach($groupedPermissions as $groupLabel => $perms)
                            <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800/70">
                                <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {{ $groupLabel }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach($perms as $perm)
                                        <li class="flex items-center justify-between text-[11px] text-gray-700 dark:text-gray-200">
                                            <label class="flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $perm->name }}"
                                                    @checked(in_array($perm->name, $selectedPermissionNames ?? []))
                                                    class="h-3.5 w-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                                />
                                                <span>{{ $perm->label }}</span>
                                            </label>
                                            <span class="ml-2 font-mono text-[10px] text-gray-400">{{ $perm->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        <div class="pt-1 text-right">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-1.5 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                            >
                                Save {{ $selectedRoleLabel ?? $selectedRoleKey }}
                            </button>
                        </div>
                    </form>
                </div>
        @elseif(!empty($selectedRoleKey) && $selectedRoleKey === 'super_admin')
                <div class="mt-5 rounded-2xl border border-brand-200 bg-brand-50 p-4 text-xs text-brand-900 shadow-theme-xs dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-100">
                    <div class="mb-1 text-sm font-semibold">
                        Super admin permissions
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        The <span class="font-semibold">Super admin</span> role automatically has access to all permissions in the system.
                        You don’t need to manage a checklist for it here. Use this page to fine‑tune access for other roles like
                        <span class="font-semibold">Admin</span>, <span class="font-semibold">Warehouse manager</span>, etc.
                    </p>
                </div>
            @endif
        </div>

    {{-- Create permission modal --}}
    <div
        x-show="showCreatePermission"
        x-cloak
        @keydown.escape.window="showCreatePermission = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 p-4"
    >
        <div
            x-show="showCreatePermission"
            x-transition
            class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Add permission</h2>
                <button type="button" @click="showCreatePermission = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="mt-4 space-y-4">
                @csrf
                <div x-data="{ keyName: '', openTray: false }">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Permission key</label>
                    <div class="relative mt-1">
                        <input
                            type="text"
                            name="name"
                            x-model="keyName"
                            placeholder="e.g. reports.view_finance"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 pr-20 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                            required
                        />
                        <button
                            type="button"
                            @click="openTray = !openTray"
                            class="absolute inset-y-0 right-1 my-1 inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-2 text-[10px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Browse
                            <svg class="ml-1 h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            x-show="openTray"
                            x-transition
                            @click.outside="openTray = false"
                            class="absolute right-0 z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                        >
                            @if($permissions->isEmpty())
                                <div class="px-3 py-2 text-[11px] text-gray-500 dark:text-gray-400">
                                    No permissions defined yet. Type a new key above.
                                </div>
                            @else
                                <div class="max-h-56 divide-y divide-gray-100 text-left text-[11px] dark:divide-gray-800">
                                    @foreach($permissions as $perm)
                                        <button
                                            type="button"
                                            @click="keyName='{{ $perm->name }}'; openTray=false"
                                            class="flex w-full items-start justify-between gap-2 px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800"
                                        >
                                            <span class="font-mono text-[11px] text-gray-900 dark:text-gray-50">
                                                {{ $perm->name }}
                                            </span>
                                            <span class="flex-1 text-right text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ $perm->label }}
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                        You can type a new key or pick one from the tray.
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label</label>
                    <input
                        type="text"
                        name="label"
                        placeholder="Visible name"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Group</label>
                    <input
                        type="text"
                        name="group"
                        list="permission-groups"
                        placeholder="e.g. Inventory & stock, Sales & returns, Access control"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <datalist id="permission-groups">
                        @foreach($groups as $group)
                            <option value="{{ $group->label }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="mt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showCreatePermission = false"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                    >
                        Save permission
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Role ⇄ permission matrix --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-x-auto">
        <form method="POST" action="{{ route('admin.permissions.roles.update') }}">
            @csrf
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Permission</th>
                        <th class="px-4 py-2 text-left">Key</th>
                        <th class="px-4 py-2 text-left">Group</th>
                        @foreach($roles as $roleKey => $roleLabel)
                            <th class="px-4 py-2 text-center">
                                <a href="{{ route('admin.permissions.index', ['role' => $roleKey]) }}"
                                   class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px]
                                          {{ isset($selectedRoleKey) && $selectedRoleKey === $roleKey
                                                ? 'bg-brand-500 text-white'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $roleLabel }}
                                </a>
                            </th>
                        @endforeach
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($permissions as $permission)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-2 text-gray-900 dark:text-white">
                                {{ $permission->label }}
                            </td>
                            <td class="px-4 py-2 font-mono text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $permission->name }}
                            </td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                {{ $permission->group ?? '—' }}
                            </td>
                            @foreach($roles as $roleKey => $roleLabel)
                                @php
                                    $allowed = in_array($permission->name, $rolePermissions[$roleKey] ?? []);
                                @endphp
                                <td class="px-4 py-2 text-center">
                                    @if($roleKey === 'super_admin')
                                        <span class="inline-flex items-center justify-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-medium text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                            All
                                        </span>
                                    @else
                                        <input
                                            type="checkbox"
                                            name="role_permissions[{{ $roleKey }}][]"
                                            value="{{ $permission->name }}"
                                            @checked($allowed)
                                            class="h-3.5 w-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                        />
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" onsubmit="return confirm('Delete permission {{ $permission->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg border border-error-200 bg-error-50 px-2.5 py-1 text-[11px] font-medium text-error-600 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300 dark:hover:bg-error-500/20"
                                    >
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3 text-right dark:border-gray-800">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                >
                    Save role permissions
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
