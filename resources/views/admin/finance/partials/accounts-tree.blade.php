@props(['nodes', 'depth' => 0, 'ancestorIds' => [], 'showBalance' => false, 'showParent' => false])

@foreach($nodes as $node)
    @php
        $pad = max(0, $node->level) * 1.25;
        $hasChildren = $node->relationLoaded('children') && $node->children->isNotEmpty();
        $childAncestorIds = array_merge($ancestorIds, [$node->id]);
        $parentName = $node->parent_id && isset($accountMap[$node->parent_id])
            ? $accountMap[$node->parent_id]->name
            : '—';
        $typeBadge = match($node->type) {
            'asset' => 'bg-emerald-50 text-emerald-700',
            'liability' => 'bg-orange-50 text-orange-700',
            'equity' => 'bg-blue-50 text-blue-700',
            'income' => 'bg-sky-50 text-sky-700',
            default => 'bg-rose-50 text-rose-700',
        };
    @endphp
    <tr
        class="hover:bg-gray-50 dark:hover:bg-gray-800/40"
        @if(count($ancestorIds) > 0)
            x-show="isVisible(@js($ancestorIds))"
            x-cloak
        @endif
    >
        <td class="px-5 py-3 font-mono text-sm text-gray-900 dark:text-white">{{ $node->code }}</td>
        <td class="px-5 py-3" style="padding-left: {{ 1.25 + $pad }}rem">
            <div class="flex items-center gap-1.5">
                @if($hasChildren)
                    <button
                        type="button"
                        @click.stop="toggle({{ $node->id }})"
                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        :aria-expanded="isExpanded({{ $node->id }})"
                        title="Expand or collapse"
                    >
                        <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="isExpanded({{ $node->id }}) ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <span class="inline-block h-5 w-5 shrink-0" aria-hidden="true"></span>
                @endif
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $node->name }}</span>
            </div>
        </td>
        @if($showParent)
            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $parentName }}</td>
        @endif
        <td class="px-5 py-3">
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $typeBadge }}">{{ ucfirst($node->type) }}</span>
        </td>
        <td class="px-5 py-3">
            @if($node->is_group)
                <span class="text-xs font-medium text-brand-600">Group</span>
            @else
                <span class="text-xs font-medium text-emerald-600">Ledger</span>
            @endif
        </td>
        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $node->slug ?: '—' }}</td>
        @if($showBalance)
            <td class="px-5 py-3 text-right font-mono text-sm text-gray-900 dark:text-white">
                @php
                    $rowBalance = isset($coaSummary, $balancePeriod, $balanceClosing)
                        ? $coaSummary->rollupBalance($node, $balancePeriod, $balanceClosing)
                        : 0;
                @endphp
                @if(abs($rowBalance) >= 0.01)
                    {{ number_format($rowBalance, 2) }}
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </td>
        @endif
        <td class="px-5 py-3 text-right">
            <div class="erp-action-group justify-end">
                @if($node->is_group)
                    <a href="{{ route('admin.accounts.create', ['parent_id' => $node->id]) }}" class="erp-btn-action">Add child</a>
                @endif
                <x-admin.action-view :href="route('admin.accounts.show', $node)" />
                <x-admin.action-edit :href="route('admin.accounts.edit', $node)" />
                <x-admin.action-delete
                    :action="route('admin.accounts.destroy', $node)"
                    :confirm="'Delete account ' . $node->code . ' — ' . $node->name . '? This cannot be undone.'"
                />
            </div>
        </td>
    </tr>
    @if($hasChildren)
        @include('admin.finance.partials.accounts-tree', [
            'nodes' => $node->children,
            'depth' => $depth + 1,
            'ancestorIds' => $childAncestorIds,
            'showBalance' => $showBalance,
            'showParent' => $showParent,
        ])
    @endif
@endforeach
