<div class="form-section">
    <div class="section-header">
        <h3>Expense details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="date">Date</label>
            <input id="date" name="date" type="date" value="{{ old('date', optional($expense->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="category">Category</label>
            @php
                $currentCategory = strtolower(old('category', $expense->category ?? 'general'));
            @endphp
            <select id="category" name="category">
                <option value="general"{{ $currentCategory === 'general' ? ' selected' : '' }}>General</option>
                <option value="marketing"{{ $currentCategory === 'marketing' ? ' selected' : '' }}>Marketing</option>
                <option value="utilities"{{ $currentCategory === 'utilities' ? ' selected' : '' }}>Utilities</option>
                <option value="salary"{{ $currentCategory === 'salary' ? ' selected' : '' }}>Salary</option>
                <option value="travel"{{ $currentCategory === 'travel' ? ' selected' : '' }}>Travel</option>
                <option value="other"{{ $currentCategory === 'other' ? ' selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <label for="amount">Amount</label>
            <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount', $expense->amount ?? '') }}" required>
        </div>
        <div>
            <label for="reference">Reference</label>
            <input id="reference" name="reference" value="{{ old('reference', $expense->reference ?? '') }}">
        </div>
        <div class="full-width">
            <label for="description">Description</label>
            <input id="description" name="description" value="{{ old('description', $expense->description ?? '') }}">
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = strtolower(old('status', $expense->status ?? 'recorded'));
            @endphp
            <select id="status" name="status">
                <option value="recorded"{{ $currentStatus === 'recorded' ? ' selected' : '' }}>Recorded</option>
                <option value="reviewed"{{ $currentStatus === 'reviewed' ? ' selected' : '' }}>Reviewed</option>
            </select>
        </div>
    </div>
</div>

