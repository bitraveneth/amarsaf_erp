<div class="form-section">
    <div class="section-header">
        <h3>Allowance details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="date">Date</label>
            <input id="date" name="date" type="date" value="{{ old('date', optional($allowance->date)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="type">Type</label>
            @php
                $currentType = strtoupper(old('type', $allowance->type ?? 'TA'));
            @endphp
            <select id="type" name="type">
                <option value="TA"{{ $currentType === 'TA' ? ' selected' : '' }}>TA (Travel Allowance)</option>
                <option value="DA"{{ $currentType === 'DA' ? ' selected' : '' }}>DA (Dearness Allowance)</option>
                <option value="BONUS"{{ $currentType === 'BONUS' ? ' selected' : '' }}>Bonus</option>
                <option value="OTHER"{{ $currentType === 'OTHER' ? ' selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <label for="reference">Reference</label>
            <input id="reference" name="reference" value="{{ old('reference', $allowance->reference ?? '') }}">
        </div>
        <div>
            <label for="amount">Amount</label>
            <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount', $allowance->amount ?? '') }}" required>
        </div>
        <div class="full-width">
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description', $allowance->description ?? '') }}</textarea>
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = old('status', $allowance->status ?? 'submitted');
            @endphp
            <select id="status" name="status">
                <option value="submitted"{{ $currentStatus === 'submitted' ? ' selected' : '' }}>Submitted</option>
                <option value="approved"{{ $currentStatus === 'approved' ? ' selected' : '' }}>Approved</option>
                <option value="rejected"{{ $currentStatus === 'rejected' ? ' selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div>
            <label for="attachment">Slip / attachment</label>
            <input id="attachment" name="attachment" type="file">
            @if(!empty($allowance->attachment_path))
                <div class="preview">
                    <small>Current:</small>
                    <a href="{{ asset('storage/'.$allowance->attachment_path) }}" target="_blank" class="button-secondary">View slip</a>
                </div>
            @endif
        </div>
    </div>
</div>

