<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatcher
{
    public function dispatch(string $event, array $payload): void
    {
        if (! class_exists(WebhookEndpoint::class)) {
            return;
        }

        $endpoints = WebhookEndpoint::query()
            ->where('is_active', true)
            ->get()
            ->filter(function (WebhookEndpoint $endpoint) use ($event) {
                $events = $endpoint->events ?? [];

                return empty($events) || in_array($event, $events, true) || in_array('*', $events, true);
            });

        foreach ($endpoints as $endpoint) {
            try {
                $body = [
                    'event' => $event,
                    'payload' => $payload,
                    'sent_at' => now()->toIso8601String(),
                ];

                $request = Http::timeout(5)->asJson();

                if ($endpoint->secret) {
                    $request = $request->withHeaders([
                        'X-Saf-Signature' => hash_hmac('sha256', json_encode($body), $endpoint->secret),
                    ]);
                }

                $request->post($endpoint->url, $body);
            } catch (\Throwable $exception) {
                Log::warning('Webhook dispatch failed', [
                    'endpoint_id' => $endpoint->id,
                    'event' => $event,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
