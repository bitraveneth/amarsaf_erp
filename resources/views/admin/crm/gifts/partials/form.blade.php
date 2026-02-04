<div class="form-section">
    <div class="section-header">
        <h3>Gift details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="date">Date</label>
            <input id="date" name="date" type="date" value="{{ old('date', optional($gift->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="agent_id">Agent</label>
            <select id="agent_id" name="agent_id" required>
                <option value="">Select agent</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ old('agent_id', $gift->agent_id ?? '') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="employee_id">Handled by (employee)</label>
            <select id="employee_id" name="employee_id">
                <option value="">—</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id', $gift->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="occasion">Occasion</label>
            <input id="occasion" name="occasion" value="{{ old('occasion', $gift->occasion ?? '') }}" placeholder="Birthday, yearly, festival, bonus">
        </div>
        <div>
            <label for="gift_type">Gift type</label>
            <input id="gift_type" name="gift_type" value="{{ old('gift_type', $gift->gift_type ?? '') }}" placeholder="Fridge, banner, gift item, rack">
        </div>
        <div>
            <label for="amount">Amount</label>
            <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount', $gift->amount ?? '') }}">
        </div>
        <div>
            <label for="campaign_code">Campaign code</label>
            <input id="campaign_code" name="campaign_code" value="{{ old('campaign_code', $gift->campaign_code ?? '') }}">
        </div>
        <div class="full-width">
            <label for="description">Description</label>
            <input id="description" name="description" value="{{ old('description', $gift->description ?? '') }}">
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = strtolower(old('status', $gift->status ?? 'given'));
            @endphp
            <select id="status" name="status">
                <option value="planned"{{ $currentStatus === 'planned' ? ' selected' : '' }}>Planned</option>
                <option value="given"{{ $currentStatus === 'given' ? ' selected' : '' }}>Given</option>
            </select>
        </div>
    </div>
</div>

