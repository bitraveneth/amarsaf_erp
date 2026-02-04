<div class="form-section">
    <div class="section-header">
        <h3>Location details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="logged_at">Date/time</label>
            <input id="logged_at" name="logged_at" type="datetime-local"
                   value="{{ old('logged_at', optional($log->logged_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div>
            <label for="location_label">Location label</label>
            <input id="location_label" name="location_label" value="{{ old('location_label', $log->location_label ?? '') }}">
            <small class="text-muted">e.g. Dealer code, area name, landmark.</small>
        </div>
        <div>
            <label for="latitude">Latitude</label>
            <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $log->latitude ?? '') }}">
        </div>
        <div>
            <label for="longitude">Longitude</label>
            <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $log->longitude ?? '') }}">
        </div>
        <div>
            <label for="source">Source</label>
            <input id="source" name="source" value="{{ old('source', $log->source ?? 'manual') }}">
        </div>
        <div class="full-width">
            <label for="notes">Notes</label>
            <input id="notes" name="notes" value="{{ old('notes', $log->notes ?? '') }}">
        </div>
    </div>
</div>

