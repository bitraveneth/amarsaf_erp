@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Commercial terms · {{ $agent->name }}</h1>
                <p>Maintain per-SKU price lists and commission rules for this agent.</p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="button-secondary">Back to agents</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.agents.pricing.update', $agent) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <div class="section-header">
                    <h3>Price list</h3>
                    <p>Override catalog prices per SKU. Leave blank to use default product price.</p>
                </div>
                <div class="form-grid">
                    @foreach($products as $product)
                        @php
                            $entry = $priceLists[$product->id] ?? null;
                        @endphp
                        <div>
                            <label>
                                {{ $product->sku }} — {{ $product->name }}
                                <span class="text-muted">Base: {{ number_format($product->base_price ?? 0, 2) }}</span>
                            </label>
                            <input
                                name="prices[{{ $product->id }}]"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('prices.' . $product->id, optional($entry)->price) }}"
                                placeholder="Use base price">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Commission rules</h3>
                    <p>Start simple: usually one row is enough (e.g. 2% on all regular orders). Leave other rows empty.</p>
                </div>

                <div class="commission-grid">
                    @php
                        $rows = old('commissions', $commissions->toArray());
                        if (count($rows) < 5) {
                            $rows = array_pad($rows, 5, []);
                        }
                    @endphp
                    @foreach($rows as $index => $row)
                        @php
                            $baseStyle = 'margin-bottom:1.5rem;padding:1rem;border-radius:0.75rem;background:#f9fafb;';
                            $isEmptyRule = empty(array_filter($row ?? []));
                            $hidden = $index > 0 && $isEmptyRule;
                        @endphp
                        <div class="commission-rule"
                             style="{{ $baseStyle }}@if($hidden)display:none;@endif"
                             @if($hidden) data-hidden="1" @endif>
                            <div class="section-header">
                                <h4>Rule {{ $index + 1 }}</h4>
                                <p class="text-muted">Leave this rule blank to ignore it.</p>
                            </div>
                            <div class="form-grid">
                                <div>
                                    <label for="commissions[{{ $index }}][sku]">Applies to SKU</label>
                                    <input name="commissions[{{ $index }}][sku]"
                                           value="{{ $row['sku'] ?? '' }}"
                                           placeholder="Blank = all SKUs">
                                    <small class="text-muted">Blank = applies to all SKUs.</small>
                                </div>
                                <div>
                                    <label for="commissions[{{ $index }}][type]">How to calculate</label>
                                    <select name="commissions[{{ $index }}][type]">
                                        <option value="">None</option>
                                        @foreach(['percentage' => '% of line', 'fixed' => 'Fixed per order'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ ($row['type'] ?? null) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">"% of line" = percent, "Fixed" = flat BDT.</small>
                                </div>
                                <div>
                                    <label for="commissions[{{ $index }}][value]">Amount</label>
                                    <input name="commissions[{{ $index }}][value]"
                                           type="number"
                                           step="0.01"
                                           min="0"
                                           value="{{ $row['value'] ?? '' }}">
                                    <small class="text-muted">%: enter percent (2 = 2). Fixed: enter BDT.</small>
                                </div>
                            </div>
                            <div class="form-grid" style="margin-top:0.75rem;">
                                <div>
                                    <label for="commissions[{{ $index }}][order_type]">Order type filter</label>
                                    <select name="commissions[{{ $index }}][order_type]">
                                        <option value="">All types</option>
                                        @foreach(['regular', 'bulk', 'sample', 'return'] as $type)
                                            <option value="{{ $type }}"
                                                {{ ($row['order_type'] ?? null) === $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Blank = all order types.</small>
                                </div>
                                <div>
                                    <label for="commissions[{{ $index }}][frequency]">When to apply</label>
                                    <select name="commissions[{{ $index }}][frequency]">
                                        @foreach(['per_order' => 'Per order', 'monthly' => 'Monthly summary'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ ($row['frequency'] ?? 'per_order') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Use "Per order" now; "Monthly" is for reports.</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="form-actions">
                    <button type="button" class="button-secondary" id="add-commission-rule">
                        Add rule
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Save commercial terms</button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-commission-rule');
    if (!addBtn) return;

    addBtn.addEventListener('click', function () {
        const nextHidden = document.querySelector('.commission-rule[data-hidden="1"]');
        if (nextHidden) {
            nextHidden.style.display = '';
            nextHidden.removeAttribute('data-hidden');
        }
    });
});
</script>
@endpush
