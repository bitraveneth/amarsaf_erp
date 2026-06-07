<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = WebhookEndpoint::orderBy('name')->get();

        return view('admin.webhooks.index', compact('webhooks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|url|max:255',
            'secret' => 'nullable|string|max:255',
            'events' => 'nullable|string',
        ]);

        WebhookEndpoint::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'secret' => $data['secret'] ?? null,
            'events' => $this->parseEvents($data['events'] ?? null),
            'is_active' => true,
        ]);

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook endpoint saved.');
    }

    public function destroy(WebhookEndpoint $webhook)
    {
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook endpoint removed.');
    }

    protected function parseEvents(?string $events): array
    {
        if ($events === null || trim($events) === '') {
            return ['*'];
        }

        return collect(explode(',', $events))
            ->map(fn ($event) => trim($event))
            ->filter()
            ->values()
            ->all();
    }
}
