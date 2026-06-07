@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="webhook"
        title="Webhook endpoints"
        subtitle="Register outbound URLs to receive events such as journal.posted from Saf ERP."
    />

    <x-admin.table-card title="Add endpoint" description="Leave events blank to receive all supported events.">
        <form method="POST" action="{{ route('admin.webhooks.store') }}" class="grid gap-4 p-4 md:grid-cols-2 md:p-5">
            @csrf
            <div class="erp-field">
                <label class="erp-label" for="webhook-name">Name</label>
                <input id="webhook-name" name="name" class="erp-input" placeholder="Accounting middleware" required>
            </div>
            <div class="erp-field">
                <label class="erp-label" for="webhook-url">URL</label>
                <input id="webhook-url" name="url" type="url" class="erp-input" placeholder="https://example.com/webhooks/saf" required>
            </div>
            <div class="erp-field">
                <label class="erp-label" for="webhook-secret">Secret (optional)</label>
                <input id="webhook-secret" name="secret" class="erp-input" placeholder="Used for HMAC signature header">
            </div>
            <div class="erp-field">
                <label class="erp-label" for="webhook-events">Events</label>
                <input id="webhook-events" name="events" class="erp-input" placeholder="journal.posted or leave blank for all">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="erp-btn-primary">Save webhook</button>
            </div>
        </form>
    </x-admin.table-card>

    <x-admin.table-card title="Active endpoints" description="Remove endpoints you no longer use.">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Events</th>
                    <th class="is-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($webhooks as $webhook)
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">{{ $webhook->name }}</td>
                        <td class="max-w-md truncate">{{ $webhook->url }}</td>
                        <td>
                            <span class="erp-badge-neutral">{{ implode(', ', $webhook->events ?? ['*']) }}</span>
                        </td>
                        <td class="is-right">
                            <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}" onsubmit="return confirm('Remove this webhook endpoint?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="erp-btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-admin.empty-state title="No webhooks configured" description="Add an endpoint above to start receiving outbound ERP events." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>
</div>
@endsection
