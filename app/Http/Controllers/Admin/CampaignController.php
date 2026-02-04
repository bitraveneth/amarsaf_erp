<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderByDesc('start_date')->paginate(15);

        return view('admin.marketing.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.marketing.campaigns.create', [
            'campaign' => new Campaign(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('marketing/campaigns', 'public');
        }

        Campaign::create($data);

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign saved.');
    }

    public function edit(Campaign $campaign)
    {
        return view('admin.marketing.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            if ($campaign->attachment_path) {
                Storage::disk('public')->delete($campaign->attachment_path);
            }

            $data['attachment_path'] = $request->file('attachment')->store('marketing/campaigns', 'public');
        }

        $campaign->update($data);

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->attachment_path) {
            Storage::disk('public')->delete($campaign->attachment_path);
        }

        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('status', 'Campaign deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reach' => 'nullable|integer|min:0',
            'impressions' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:50',
            'campaign_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);
    }
}
