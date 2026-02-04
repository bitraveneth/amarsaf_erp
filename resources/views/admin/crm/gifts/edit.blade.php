@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit customer gift</h1>
                <p>Update gift details or status.</p>
            </div>
            <a href="{{ route('admin.gifts.index') }}" class="button-secondary">Back to gifts</a>
        </header>

        <form action="{{ route('admin.gifts.update', $gift) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.crm.gifts.partials.form', ['gift' => $gift, 'agents' => $agents, 'employees' => $employees])
            <div class="form-actions">
                <button type="submit">Update gift</button>
            </div>
        </form>
    </section>
</div>
@endsection

