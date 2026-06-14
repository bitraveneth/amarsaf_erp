<?php

namespace App\Services\Manufacturing;

use App\Helpers\Permission;
use App\Models\BillOfMaterial;
use App\Models\NotificationDispatchLog;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Schema;

class ManufacturingNotificationService
{
    public function notifyBomCreated(BillOfMaterial $bom, bool $activated = false): void
    {
        $bom->loadMissing(['product', 'items']);

        $productLabel = $this->productLabel($bom);
        $componentCount = $bom->items->count();
        $actorName = auth()->user()?->name ?? 'A user';

        if ($activated) {
            $this->notifyUsersWithPermission('manufacturing.manage', [
                'key' => 'bom_activated_' . $bom->id,
                'title' => 'Recipe activated',
                'message' => "{$actorName} activated \"{$bom->displayName()}\" for {$productLabel} ({$componentCount} components). Production can use this recipe.",
                'variant' => 'success',
                'source' => 'Manufacturing',
                'link' => route('admin.boms.show', $bom),
                'context' => [
                    'bom_id' => $bom->id,
                    'product_id' => $bom->product_id,
                    'activated' => true,
                ],
            ]);

            return;
        }

        $this->notifyUsersWithPermission('manufacturing.manage', [
            'key' => 'bom_draft_' . $bom->id,
            'title' => 'Draft recipe saved',
            'message' => "{$actorName} saved a draft BOM for {$productLabel} ({$componentCount} components). Activate when ready for production.",
            'variant' => 'info',
            'source' => 'Manufacturing',
            'link' => route('admin.boms.show', $bom),
            'context' => [
                'bom_id' => $bom->id,
                'product_id' => $bom->product_id,
                'activated' => false,
            ],
        ]);
    }

    public function notifyBomActivated(BillOfMaterial $bom): void
    {
        $this->notifyBomCreated($bom->fresh(['product', 'items']), activated: true);
    }

    protected function productLabel(BillOfMaterial $bom): string
    {
        $sku = trim((string) ($bom->product?->sku ?? ''));
        $name = trim((string) ($bom->product?->name ?? 'Product'));

        return $sku !== '' ? "{$sku} – {$name}" : $name;
    }

    protected function notifyUsersWithPermission(string $permission, array $payload): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $dedupeKey = (string) ($payload['key'] ?? '');
        if ($dedupeKey === '') {
            return;
        }

        $recipients = User::query()
            ->get()
            ->filter(fn (User $user) => Permission::can($user, $permission));

        foreach ($recipients as $user) {
            $dispatchLog = NotificationDispatchLog::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'dedupe_key' => $dedupeKey,
                ],
                [
                    'sent_at' => now(),
                ]
            );

            if (! $dispatchLog->wasRecentlyCreated) {
                continue;
            }

            $user->notify(new SystemAlertNotification($payload));
        }
    }
}
