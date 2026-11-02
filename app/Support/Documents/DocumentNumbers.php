<?php

namespace App\Support\Documents;

use App\Models\Delivery;
use App\Models\Invoice;
use Carbon\Carbon;
use DateTimeInterface;

class DocumentNumbers
{
    public static function invoiceCode(DateTimeInterface $issuedAt, int $sequence): string
    {
        return 'SFIN'.Carbon::parse($issuedAt)->format('Ymd').str_pad((string) max(1, $sequence), 2, '0', STR_PAD_LEFT);
    }

    public static function challanCode(DateTimeInterface $date, int $sequence, int $countThatDay): string
    {
        $base = 'SGC'.Carbon::parse($date)->format('Ymd');

        if ($countThatDay <= 1 || $sequence <= 1) {
            return $base;
        }

        return $base.str_pad((string) max(1, $sequence), 2, '0', STR_PAD_LEFT);
    }

    public static function nextInvoice(DateTimeInterface $issuedAt, ?int $ignoreId = null): string
    {
        $sequence = 1;

        do {
            $number = self::invoiceCode($issuedAt, $sequence);
            $sequence++;
            $taken = Invoice::query()
                ->where('number', $number)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists();
        } while ($taken && $sequence < 1000);

        return $number;
    }

    public static function challan(Delivery $delivery): string
    {
        $delivery->loadMissing('order');
        $when = Carbon::parse($delivery->order?->delivery_date ?? $delivery->created_at ?? now());
        $day = $when->toDateString();

        $ids = Delivery::query()
            ->leftJoin('orders', 'orders.id', '=', 'deliveries.order_id')
            ->where(function ($query) use ($day) {
                $query->whereDate('orders.delivery_date', $day)
                    ->orWhere(function ($query) use ($day) {
                        $query->whereNull('orders.delivery_date')
                            ->whereDate('deliveries.created_at', $day);
                    });
            })
            ->orderBy('deliveries.id')
            ->pluck('deliveries.id');

        $position = $ids->search($delivery->id);
        $sequence = $position === false ? 1 : ((int) $position + 1);

        return self::challanCode($when, $sequence, max(1, $ids->count()));
    }
}
