@php
    $inbox = $procurementInbox ?? [];
    $show = ($inbox['needs_my_approval'] ?? 0) > 0
        || (($inbox['awaiting_receipt'] ?? 0) > 0 && ($inbox['can_create_grn'] ?? false));
@endphp

@if($show)
    <section {{ $attributes->merge(['class' => 'dash-procurement mt-2']) }}>
        <x-dashboard.section-header
            title="Procurement inbox"
            description="Purchase orders and GRNs that need receiving or sign-off."
            class="mb-4"
        >
            <x-slot:actions>
                <a href="{{ route('admin.goods-receipts.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    Open GRN inbox
                </a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="dash-procurement__grid">
            @if(($inbox['needs_my_approval'] ?? 0) > 0)
                <a href="{{ route('admin.goods-receipts.index', ['tab' => 'pending']) }}" class="dash-procurement__card dash-procurement__card--warn">
                    <span class="dash-procurement__count">{{ $inbox['needs_my_approval'] }}</span>
                    <span class="dash-procurement__label">GRN sign-off{{ $inbox['needs_my_approval'] === 1 ? '' : 's' }} waiting for you</span>
                    <span class="dash-procurement__cta">Review pending →</span>
                </a>
            @endif
            @if(($inbox['awaiting_receipt'] ?? 0) > 0 && ($inbox['can_create_grn'] ?? false))
                <a href="{{ route('admin.goods-receipts.index', ['tab' => 'awaiting']) }}" class="dash-procurement__card dash-procurement__card--info">
                    <span class="dash-procurement__count">{{ $inbox['awaiting_receipt'] }}</span>
                    <span class="dash-procurement__label">PO{{ $inbox['awaiting_receipt'] === 1 ? '' : 's' }} ready to receive</span>
                    <span class="dash-procurement__cta">Awaiting receipt →</span>
                </a>
            @endif
        </div>
    </section>
@endif
