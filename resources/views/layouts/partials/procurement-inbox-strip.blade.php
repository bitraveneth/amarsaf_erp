@php
    $inbox = $procurementInbox ?? [];
    $show = ($inbox['needs_my_approval'] ?? 0) > 0
        || (($inbox['awaiting_receipt'] ?? 0) > 0 && ($inbox['can_create_grn'] ?? false));
@endphp

@if($show)
    <div class="erp-proc-inbox mb-5">
        <div class="erp-proc-inbox__head">
            <p class="erp-proc-inbox__title">Procurement inbox</p>
            <p class="erp-proc-inbox__desc">Items that need your attention right now.</p>
        </div>
        <div class="erp-proc-inbox__grid">
            @if(($inbox['needs_my_approval'] ?? 0) > 0)
                <a href="{{ route('admin.goods-receipts.index', ['tab' => 'pending']) }}" class="erp-proc-inbox__card erp-proc-inbox__card--warn">
                    <span class="erp-proc-inbox__count">{{ $inbox['needs_my_approval'] }}</span>
                    <span class="erp-proc-inbox__label">GRN sign-off{{ $inbox['needs_my_approval'] === 1 ? '' : 's' }} waiting for you</span>
                    <span class="erp-proc-inbox__cta">Review pending GRNs →</span>
                </a>
            @endif
            @if(($inbox['awaiting_receipt'] ?? 0) > 0 && ($inbox['can_create_grn'] ?? false))
                <a href="{{ route('admin.goods-receipts.index', ['tab' => 'awaiting']) }}" class="erp-proc-inbox__card erp-proc-inbox__card--info">
                    <span class="erp-proc-inbox__count">{{ $inbox['awaiting_receipt'] }}</span>
                    <span class="erp-proc-inbox__label">PO{{ $inbox['awaiting_receipt'] === 1 ? '' : 's' }} ready to receive</span>
                    <span class="erp-proc-inbox__cta">Open awaiting receipt →</span>
                </a>
            @endif
        </div>
    </div>
@endif
