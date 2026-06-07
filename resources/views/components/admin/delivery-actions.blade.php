@props([
    'delivery',
])

@php
    $deleteConfirm = 'Delete delivery #' . $delivery->id . ' for order #' . $delivery->order_id . '? This action cannot be undone.';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-2']) }}>
    <x-admin.action-group class="justify-end">
        <x-admin.action-view :href="route('admin.deliveries.show', $delivery)" />
        <x-admin.action-edit :href="route('admin.deliveries.edit', $delivery)" />
        <x-admin.action-delete
            :action="route('admin.deliveries.destroy', $delivery)"
            :confirm="$deleteConfirm"
        />
    </x-admin.action-group>

    <x-admin.action-menu label="Docs">
        <x-admin.action-menu-label>Print &amp; download</x-admin.action-menu-label>

        <x-admin.action-menu-item :href="route('admin.deliveries.packing-slip', $delivery)" :external="true">
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            Packing slip
        </x-admin.action-menu-item>

        @if(Route::has('admin.documents.preview'))
            <x-admin.action-menu-item :href="route('admin.documents.preview', ['type' => 'delivery-challan', 'id' => $delivery->id])" :external="true">
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                </svg>
                Delivery challan
            </x-admin.action-menu-item>
        @endif

        <x-admin.action-menu-item :href="route('admin.deliveries.pod-pdf', $delivery)">
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            POD PDF
        </x-admin.action-menu-item>
    </x-admin.action-menu>
</div>
