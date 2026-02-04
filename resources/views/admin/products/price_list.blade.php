@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Price lists</h1>
                <p>See base prices and which agents have negotiated overrides for each SKU.</p>
            </div>
        </header>

        @if($products->isNotEmpty())
            <table class="data-table">
                <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Base price</th>
                    <th>Agents with overrides</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->base_price ?? 0, 2) }}</td>
                        <td>
                            @if($product->agent_price_lists_count > 0)
                                {{ $product->agent_price_lists_count }} agent(s)
                            @else
                                <span class="text-muted">No overrides</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.products.prices.show', $product) }}" class="button-secondary">
                                View details
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $products->links() }}
        @else
            <p class="panel-note">
                No products found. Add products first, then maintain price lists.
            </p>
        @endif
    </section>
</div>
@endsection

