<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Batch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    public function search(string $query, int $limit = 10): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
        $results = collect();

        Product::query()
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            })
            ->limit($limit)
            ->get()
            ->each(function (Product $product) use ($results) {
                $results->push([
                    'label' => $product->name,
                    'group' => 'Product · ' . ($product->sku ?: 'No SKU'),
                    'path' => url('/admin/products/' . $product->id . '/edit'),
                ]);
            });

        Order::query()
            ->where(function ($builder) use ($like, $query) {
                $builder->where('agent_reference', 'like', $like);
                if (is_numeric($query)) {
                    $builder->orWhere('id', (int) $query);
                }
            })
            ->limit($limit)
            ->get()
            ->each(function (Order $order) use ($results) {
                $label = $order->agent_reference
                    ? 'Order ' . $order->agent_reference
                    : 'Order #' . $order->id;

                $results->push([
                    'label' => $label,
                    'group' => 'Sales · ' . ucfirst($order->status),
                    'path' => url('/admin/orders/' . $order->id),
                ]);
            });

        Invoice::query()
            ->where('number', 'like', $like)
            ->limit($limit)
            ->get()
            ->each(function (Invoice $invoice) use ($results) {
                $results->push([
                    'label' => $invoice->number,
                    'group' => 'Finance · ' . ucfirst($invoice->status),
                    'path' => url('/admin/finance/' . $invoice->id),
                ]);
            });

        Agent::query()
            ->where('name', 'like', $like)
            ->limit($limit)
            ->get()
            ->each(function (Agent $agent) use ($results) {
                $results->push([
                    'label' => $agent->name,
                    'group' => 'Agent',
                    'path' => url('/admin/agents/' . $agent->id . '/ledger'),
                ]);
            });

        Batch::query()
            ->where('batch_code', 'like', $like)
            ->limit($limit)
            ->get()
            ->each(function (Batch $batch) use ($results) {
                $results->push([
                    'label' => $batch->batch_code,
                    'group' => 'Batch · ' . ($batch->product?->name ?? 'Product'),
                    'path' => url('/admin/reports/batch-trace/' . $batch->id),
                ]);
            });

        return $results->take($limit)->values();
    }
}
