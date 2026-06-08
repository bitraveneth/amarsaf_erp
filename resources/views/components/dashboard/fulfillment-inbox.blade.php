@php
    $inbox = $fulfillmentInbox ?? [];
    $show = ($inbox['needs_my_action'] ?? 0) > 0;
@endphp

@if($show)
    <section {{ $attributes->merge(['class' => 'dash-procurement mt-2']) }}>
        <x-dashboard.section-header
            title="Fulfillment inbox"
            description="Sales orders waiting for confirm, pick, pack, or delivery."
            class="mb-4"
        >
            <x-slot:actions>
                <a href="{{ route('admin.orders.picking-overview') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    Picking lists
                </a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="dash-procurement__grid">
            @if(($inbox['needs_my_confirm'] ?? 0) > 0)
                <a href="{{ route('admin.orders.index') }}" class="dash-procurement__card dash-procurement__card--info">
                    <span class="dash-procurement__count">{{ $inbox['needs_my_confirm'] }}</span>
                    <span class="dash-procurement__label">Order{{ $inbox['needs_my_confirm'] === 1 ? '' : 's' }} to confirm</span>
                    <span class="dash-procurement__cta">Sales orders →</span>
                </a>
            @endif
            @if(($inbox['needs_my_pick'] ?? 0) > 0)
                <a href="{{ route('admin.orders.picking-overview') }}" class="dash-procurement__card dash-procurement__card--warn">
                    <span class="dash-procurement__count">{{ $inbox['needs_my_pick'] }}</span>
                    <span class="dash-procurement__label">Order{{ $inbox['needs_my_pick'] === 1 ? '' : 's' }} to pick</span>
                    <span class="dash-procurement__cta">Picking lists →</span>
                </a>
            @endif
            @if(($inbox['needs_my_pack'] ?? 0) > 0)
                <a href="{{ route('admin.orders.index') }}" class="dash-procurement__card dash-procurement__card--warn">
                    <span class="dash-procurement__count">{{ $inbox['needs_my_pack'] }}</span>
                    <span class="dash-procurement__label">Order{{ $inbox['needs_my_pack'] === 1 ? '' : 's' }} to pack</span>
                    <span class="dash-procurement__cta">Review orders →</span>
                </a>
            @endif
            @if(($inbox['needs_my_delivery'] ?? 0) > 0)
                <a href="{{ route('admin.deliveries.pod-index') }}" class="dash-procurement__card dash-procurement__card--info">
                    <span class="dash-procurement__count">{{ $inbox['needs_my_delivery'] }}</span>
                    <span class="dash-procurement__label">Delivery / POD action{{ $inbox['needs_my_delivery'] === 1 ? '' : 's' }}</span>
                    <span class="dash-procurement__cta">Deliveries & POD →</span>
                </a>
            @endif
        </div>
    </section>
@endif
