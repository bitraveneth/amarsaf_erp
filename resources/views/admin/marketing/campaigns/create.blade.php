@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add campaign</h1>
                <p>Record a marketing campaign and its performance metrics.</p>
            </div>
            <a href="{{ route('admin.campaigns.index') }}" class="button-secondary">Back to campaigns</a>
        </header>

        <form action="{{ route('admin.campaigns.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @include('admin.marketing.campaigns.partials.form', ['campaign' => $campaign])
            <div class="form-actions">
                <button type="submit">Save campaign</button>
            </div>
        </form>
    </section>
</div>
@endsection

