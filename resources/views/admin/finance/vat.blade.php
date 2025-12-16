@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>VAT summary</h1>
                <p>VAT payable for {{ $month->format('F Y') }} (from ledger).</p>
            </div>
        </header>

        <p class="panel-note">
            VAT payable: {{ number_format($vatCollected, 2) }}
        </p>
    </section>
</div>
@endsection

