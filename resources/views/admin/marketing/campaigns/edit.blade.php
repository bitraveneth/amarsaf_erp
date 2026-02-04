@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit campaign</h1>
                <p>Update campaign details or upload a report.</p>
            </div>
            <a href="{{ route('admin.campaigns.index') }}" class="button-secondary">Back to campaigns</a>
        </header>

        <form action="{{ route('admin.campaigns.update', $campaign) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('admin.marketing.campaigns.partials.form', ['campaign' => $campaign])
            <div class="form-actions">
                <button type="submit">Update campaign</button>
            </div>
        </form>
    </section>
</div>
@endsection

