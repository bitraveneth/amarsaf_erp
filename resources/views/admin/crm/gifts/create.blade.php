@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add customer gift</h1>
                <p>Record a gift for an agent/dealer.</p>
            </div>
            <a href="{{ route('admin.gifts.index') }}" class="button-secondary">Back to gifts</a>
        </header>

        <form action="{{ route('admin.gifts.store') }}" method="POST" class="form-form">
            @csrf
            @include('admin.crm.gifts.partials.form', ['gift' => $gift, 'agents' => $agents, 'employees' => $employees])
            <div class="form-actions">
                <button type="submit">Save gift</button>
            </div>
        </form>
    </section>
</div>
@endsection

