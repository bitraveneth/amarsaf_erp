@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit production run</h1>
                <p>{{ $run->product->name }} · Batch {{ $run->batch->batch_code }}</p>
            </div>
            <a href="{{ route('admin.production.index') }}" class="button-secondary">Back to runs</a>
        </header>

        <form action="{{ route('admin.production.update', $run) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <div class="section-header">
                    <h3>Overview (read-only)</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label>Warehouse</label>
                        <p class="panel-note">{{ $run->warehouse->name ?? 'Unassigned' }}</p>
                    </div>
                    <div>
                        <label>Quantity</label>
                        <p class="panel-note">{{ $run->quantity }}</p>
                    </div>
                    <div>
                        <label>QC status</label>
                        <p class="panel-note">{{ ucfirst($run->qc_status) }}</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Line &amp; notes</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="line">Production line</label>
                        <input id="line" name="line" value="{{ old('line', $run->line) }}">
                    </div>
                    <div>
                        <label for="shift">Shift</label>
                        <input id="shift" name="shift" value="{{ old('shift', $run->shift) }}">
                    </div>
                    <div class="full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes', $run->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection

