<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportCarrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogisticsCarrierController extends Controller
{
    public function index()
    {
        $carriers = TransportCarrier::query()
            ->withCount(['carrierRateCards', 'logisticsBills'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.logistics.carriers.index', compact('carriers'));
    }

    public function create()
    {
        return view('admin.logistics.carriers.create', [
            'carrier' => new TransportCarrier(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        TransportCarrier::create($this->validated($request));

        return redirect()
            ->route('admin.logistics.carriers.index')
            ->with('status', 'Transport carrier added.');
    }

    public function edit(TransportCarrier $carrier)
    {
        return view('admin.logistics.carriers.edit', compact('carrier'));
    }

    public function update(Request $request, TransportCarrier $carrier)
    {
        $carrier->update($this->validated($request, $carrier));

        return redirect()
            ->route('admin.logistics.carriers.index')
            ->with('status', 'Transport carrier updated.');
    }

    public function destroy(TransportCarrier $carrier)
    {
        if ($carrier->logisticsBills()->exists() || $carrier->carrierRateCards()->exists()) {
            return redirect()
                ->route('admin.logistics.carriers.index')
                ->withErrors(['carrier' => 'This carrier has bills or rate cards and cannot be deleted. Mark inactive instead.']);
        }

        $carrier->delete();

        return redirect()
            ->route('admin.logistics.carriers.index')
            ->with('status', 'Transport carrier removed.');
    }

    protected function validated(Request $request, ?TransportCarrier $carrier = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('transport_carriers', 'name')->ignore($carrier?->id),
            ],
            'contact_person' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('transport_carriers', 'email')->ignore($carrier?->id),
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'tax_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('transport_carriers', 'tax_id')->ignore($carrier?->id),
            ],
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
