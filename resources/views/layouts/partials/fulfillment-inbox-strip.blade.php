@php
    $inbox = $fulfillmentInbox ?? [];
    $show = ($inbox['needs_my_action'] ?? 0) > 0;
@endphp

@if($show)
    <div class="erp-proc-inbox mb-5">
        <div class="erp-proc-inbox__head">
            <p class="erp-proc-inbox__title">Fulfillment inbox</p>
            <p class="erp-proc-inbox__desc">Sales orders waiting for your next step.</p>
        </div>
        <div class="erp-proc-inbox__grid">
            @if(($inbox['needs_my_confirm'] ?? 0) > 0)
                <a href="{{ route('admin.orders.index') }}" class="erp-proc-inbox__card erp-proc-inbox__card--info">
                    <span class="erp-proc-inbox__count">{{ $inbox['needs_my_confirm'] }}</span>
                    <span class="erp-proc-inbox__label">Order{{ $inbox['needs_my_confirm'] === 1 ? '' : 's' }} to confirm</span>
                    <span class="erp-proc-inbox__cta">Open sales orders →</span>
                </a>
            @endif
            @if(($inbox['needs_my_pick'] ?? 0) > 0)
                <a href="{{ route('admin.orders.picking-overview') }}" class="erp-proc-inbox__card erp-proc-inbox__card--warn">
                    <span class="erp-proc-inbox__count">{{ $inbox['needs_my_pick'] }}</span>
                    <span class="erp-proc-inbox__label">Order{{ $inbox['needs_my_pick'] === 1 ? '' : 's' }} to pick</span>
                    <span class="erp-proc-inbox__cta">Open picking lists →</span>
                </a>
            @endif
            @if(($inbox['needs_my_pack'] ?? 0) > 0)
                <a href="{{ route('admin.orders.index') }}" class="erp-proc-inbox__card erp-proc-inbox__card--warn">
                    <span class="erp-proc-inbox__count">{{ $inbox['needs_my_pack'] }}</span>
                    <span class="erp-proc-inbox__label">Order{{ $inbox['needs_my_pack'] === 1 ? '' : 's' }} to pack</span>
                    <span class="erp-proc-inbox__cta">Review packed queue →</span>
                </a>
            @endif
            @if(($inbox['needs_my_delivery'] ?? 0) > 0)
                <a href="{{ route('admin.deliveries.pod-index') }}" class="erp-proc-inbox__card erp-proc-inbox__card--info">
                    <span class="erp-proc-inbox__count">{{ $inbox['needs_my_delivery'] }}</span>
                    <span class="erp-proc-inbox__label">Delivery / POD action{{ $inbox['needs_my_delivery'] === 1 ? '' : 's' }}</span>
                    <span class="erp-proc-inbox__cta">Open deliveries & POD →</span>
                </a>
            @endif
        </div>
    </div>
@endif
