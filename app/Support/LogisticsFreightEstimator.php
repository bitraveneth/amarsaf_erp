<?php

namespace App\Support;

use App\Models\CarrierRateCard;
use App\Models\DeliveryRoute;
use App\Models\TransportCarrier;
use Illuminate\Support\Collection;

class LogisticsFreightEstimator
{
    public function activeRateCards(): Collection
    {
        return CarrierRateCard::query()
            ->with(['transportCarrier', 'deliveryRoute'])
            ->where('is_active', true)
            ->get();
    }

    public function bestRate(?int $carrierId, ?int $routeId): ?CarrierRateCard
    {
        $cards = $this->activeRateCards();

        if ($carrierId && $routeId) {
            $exact = $cards->first(fn (CarrierRateCard $card) => (int) $card->transport_carrier_id === $carrierId
                && (int) $card->delivery_route_id === $routeId);

            if ($exact) {
                return $exact;
            }
        }

        if ($routeId) {
            $routeMatch = $cards->first(fn (CarrierRateCard $card) => (int) $card->delivery_route_id === $routeId);

            if ($routeMatch) {
                return $routeMatch;
            }
        }

        if ($carrierId) {
            return $cards->first(fn (CarrierRateCard $card) => (int) $card->transport_carrier_id === $carrierId
                && $card->delivery_route_id === null);
        }

        return null;
    }

    public function suggest(?int $routeId, int $crates = 0, ?int $carrierId = null): ?array
    {
        $card = $this->bestRate($carrierId, $routeId);

        if (! $card) {
            return null;
        }

        $amount = $card->estimateAmount($crates);

        return [
            'carrier_id' => $card->transport_carrier_id,
            'carrier_name' => $card->transportCarrier?->name,
            'route_id' => $routeId,
            'route_name' => $routeId ? DeliveryRoute::find($routeId)?->name : null,
            'unit' => $card->unit,
            'unit_label' => $card->unitLabel(),
            'rate' => (float) $card->rate,
            'crates' => $crates,
            'amount' => $amount,
            'description' => trim(sprintf(
                '%s freight — %s @ BDT %s',
                $card->transportCarrier?->name ?? 'Carrier',
                $card->unitLabel(),
                number_format($card->rate, 2)
            )),
        ];
    }
}
