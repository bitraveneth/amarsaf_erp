@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>BOMs (Bill of Materials)</h1>
                <p>Define how many bottles, caps, labels, etc. are required to produce one unit of a finished product.</p>
            </div>
            <a href="{{ route('admin.boms.create') }}" class="button-primary">Add BOM</a>
        </header>

        <table class="data-table">
            <thead>
            <tr>
                <th>Product</th>
                <th>BOM name</th>
                <th>Components</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($boms as $bom)
                <tr>
                    <td>{{ $bom->product?->name }}</td>
                    <td>{{ $bom->name ?: 'Default' }}</td>
                    <td>
                        @foreach($bom->items as $item)
                            <div>
                                {{ $item->component?->name }} –
                                {{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}
                                {{ $item->unit }}
                            </div>
                        @endforeach
                    </td>
                    <td>{{ $bom->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <form action="{{ route('admin.boms.destroy', $bom) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <div class="button-group">
                                <a href="{{ route('admin.boms.edit', $bom) }}" class="button-secondary">Edit</a>
                                <button type="submit" class="button-secondary"
                                        onclick="return confirm('Delete this BOM?')">Delete</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No BOMs defined yet. Use the “Add BOM” button above to create one.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $boms->links() }}
    </section>
</div>
@endsection
