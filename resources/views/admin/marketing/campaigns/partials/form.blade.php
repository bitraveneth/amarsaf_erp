<div class="form-section">
    <div class="section-header">
        <h3>Campaign basics</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $campaign->name ?? '') }}" required>
        </div>
        <div>
            <label for="platform">Platform</label>
            @php
                $currentPlatform = strtolower(old('platform', $campaign->platform ?? 'facebook'));
            @endphp
            <select id="platform" name="platform">
                <option value="facebook"{{ $currentPlatform === 'facebook' ? ' selected' : '' }}>Facebook</option>
                <option value="instagram"{{ $currentPlatform === 'instagram' ? ' selected' : '' }}>Instagram</option>
                <option value="google-ads"{{ $currentPlatform === 'google-ads' ? ' selected' : '' }}>Google Ads</option>
                <option value="offline"{{ $currentPlatform === 'offline' ? ' selected' : '' }}>Offline (banner/flyer/fridge)</option>
                <option value="other"{{ $currentPlatform === 'other' ? ' selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <label for="campaign_code">Campaign code</label>
            <input id="campaign_code" name="campaign_code" value="{{ old('campaign_code', $campaign->campaign_code ?? '') }}">
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = strtolower(old('status', $campaign->status ?? 'planned'));
            @endphp
            <select id="status" name="status">
                <option value="planned"{{ $currentStatus === 'planned' ? ' selected' : '' }}>Planned</option>
                <option value="running"{{ $currentStatus === 'running' ? ' selected' : '' }}>Running</option>
                <option value="completed"{{ $currentStatus === 'completed' ? ' selected' : '' }}>Completed</option>
            </select>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Dates & performance</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="start_date">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($campaign->start_date)->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="end_date">End date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', optional($campaign->end_date)->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="reach">Reach</label>
            <input id="reach" name="reach" type="number" step="1" value="{{ old('reach', $campaign->reach ?? '') }}">
        </div>
        <div>
            <label for="impressions">Impressions</label>
            <input id="impressions" name="impressions" type="number" step="1" value="{{ old('impressions', $campaign->impressions ?? '') }}">
        </div>
        <div>
            <label for="cost">Cost</label>
            <input id="cost" name="cost" type="number" step="0.01" value="{{ old('cost', $campaign->cost ?? '') }}">
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Attachments & notes</h3>
    </div>
    <div class="form-grid">
        <div class="full-width">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes', $campaign->notes ?? '') }}</textarea>
        </div>
        <div>
            <label for="attachment">Report / creative</label>
            <input id="attachment" name="attachment" type="file">
            @if(!empty($campaign->attachment_path))
                <div class="preview">
                    <small>Current:</small>
                    <a href="{{ asset('storage/'.$campaign->attachment_path) }}" target="_blank" class="button-secondary">View attachment</a>
                </div>
            @endif
        </div>
    </div>
</div>

