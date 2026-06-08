<?php

namespace App\Services\Delivery;

use App\Models\Delivery;
use App\Models\DeliveryRoute;
use Illuminate\Validation\ValidationException;

class RouteVehicleValidator
{
    /**
     * Keep the user's route/vehicle choices. Route default vehicle is only a fallback
     * when no vehicle was selected. Enforce: one truck → one active route at a time.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeDeliverySelection(array $data, ?Delivery $delivery = null): array
    {
        $routeId = array_key_exists('route_id', $data)
            ? $data['route_id']
            : $delivery?->route_id;
        $vehicleId = array_key_exists('vehicle_id', $data)
            ? $data['vehicle_id']
            : $delivery?->vehicle_id;

        if ($routeId && ! $vehicleId) {
            $route = DeliveryRoute::find($routeId);
            if ($route?->vehicle_id) {
                $vehicleId = (int) $route->vehicle_id;
            }
        }

        if ($vehicleId && $routeId) {
            $this->assertVehicleUsesSingleRoute((int) $vehicleId, (int) $routeId, $delivery?->id);
        }

        $data['route_id'] = $routeId ?: null;
        $data['vehicle_id'] = $vehicleId ?: null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeScheduleSelection(array $data): array
    {
        $vehicleId = $data['vehicle_id'] ?? null;

        if (! empty($data['route_id']) && ! $vehicleId) {
            $route = DeliveryRoute::find($data['route_id']);
            if ($route?->vehicle_id) {
                $vehicleId = (int) $route->vehicle_id;
            }
        }

        if (! $vehicleId) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Select a vehicle for this schedule.',
            ]);
        }

        if (! empty($data['route_id'])) {
            $this->assertVehicleUsesSingleRoute((int) $vehicleId, (int) $data['route_id']);
        }

        $data['vehicle_id'] = $vehicleId;

        return $data;
    }

    public function assertVehicleUsesSingleRoute(int $vehicleId, int $routeId, ?int $exceptDeliveryId = null): void
    {
        $conflict = Delivery::query()
            ->with('route')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('route_id')
            ->where('route_id', '!=', $routeId)
            ->whereIn('status', ['scheduled', 'in_transit', 'exception'])
            ->when($exceptDeliveryId, fn ($query) => $query->where('id', '!=', $exceptDeliveryId))
            ->first();

        if ($conflict) {
            $routeName = $conflict->route?->name ?? 'another route';

            throw ValidationException::withMessages([
                'vehicle_id' => "This vehicle is already assigned to {$routeName}. A truck can only run one route at a time.",
            ]);
        }
    }
}
