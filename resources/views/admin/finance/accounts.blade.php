@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Chart of accounts</h1>
                <p>Maintain the list of GL accounts used in ledger entries and reports.</p>
            </div>
            <a href="{{ route('admin.accounts.create') }}" class="button-primary">Add account</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($accounts->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td>{{ ucfirst($account->type) }}</td>
                            <td>{{ $account->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <a href="{{ route('admin.accounts.edit', $account) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.accounts.destroy', $account) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No accounts defined yet.</p>
        @endif
    </section>
</div>
@endsection

