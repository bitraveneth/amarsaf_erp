<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarrierRateCard;
use App\Models\DeliveryRoute;
use App\Models\TransportCarrier;
use Illuminate\Http\Request;

class CarrierRateCardController extends Controller
{
    public function index()
    {
        $cards = CarrierRateCard::with(['transportCarrier', 'deliveryRoute'])
            ->orderByDesc('is_active')
            ->orderBy('transport_carrier_id')
            ->paginate(20);

        return view('admin.logistics.rate_cards.index', compact('cards'));
    }

    public function create()
    {
        return view('admin.logistics.rate_cards.create', [
            'card' => new CarrierRateCard(['unit' => CarrierRateCard::UNIT_TRIP, 'is_active' => true]),
            'carriers' => TransportCarrier::active()->orderBy('name')->get(),
            'routes' => DeliveryRoute::orderBy('name')->get(),
            'units' => CarrierRateCard::units(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        CarrierRateCard::create($data);

        return redirect()->route('admin.carrier-rate-cards.index')->with('status', 'Rate card saved.');
    }

    public function edit(CarrierRateCard $carrierRateCard)
    {
        $carrierRateCard->load('transportCarrier');

        return view('admin.logistics.rate_cards.edit', [
            'card' => $carrierRateCard,
            'carriers' => TransportCarrier::active()->orderBy('name')->get(),
            'routes' => DeliveryRoute::orderBy('name')->get(),
            'units' => CarrierRateCard::units(),
        ]);
    }

    public function update(Request $request, CarrierRateCard $carrierRateCard)
    {
        $carrierRateCard->update($this->validated($request));

        return redirect()->route('admin.carrier-rate-cards.index')->with('status', 'Rate card updated.');
    }

    public function destroy(CarrierRateCard $carrierRateCard)
    {
        $carrierRateCard->delete();

        return redirect()->route('admin.carrier-rate-cards.index')->with('status', 'Rate card removed.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'transport_carrier_id' => 'required|exists:transport_carriers,id',
            'delivery_route_id' => 'nullable|exists:delivery_routes,id',
            'unit' => 'required|in:' . implode(',', array_keys(CarrierRateCard::units())),
            'rate' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
