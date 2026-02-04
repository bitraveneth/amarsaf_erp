<div class="form-section">
    <div class="section-header">
        <h3>Badge details</h3>
    </div>
        <div class="form-grid">
        <div>
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $badge->name ?? '') }}" required placeholder="e.g. Top SR of the Month">
        </div>
        <div>
            <label for="code">Code</label>
            <input id="code" name="code" value="{{ old('code', $badge->code ?? '') }}" required placeholder="e.g. TOP_SR">
            <small class="text-muted">Short unique code, e.g. TOP_SR, BEST_SELLER.</small>
        </div>
        <div class="full-width">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2" placeholder="What this badge recognises">{{ old('description', $badge->description ?? '') }}</textarea>
        </div>
        <div>
            <label for="color">Color</label>
            @php
                $currentColor = old('color', $badge->color ?? '#1d4ed8');
            @endphp
            <input id="color" name="color" type="color" value="{{ $currentColor }}">
            <small class="text-muted">Badge highlight color (currently {{ $currentColor }}).</small>
        </div>
        <div>
            <label>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $badge->is_active ?? true) ? 'checked' : '' }}>
                Active
            </label>
        </div>
    </div>
</div>
