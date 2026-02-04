<div class="form-section">
    <div class="section-header">
        <h3>Leave details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="start_date">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($leave->start_date)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="end_date">End date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', optional($leave->end_date)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="type">Type</label>
            @php
                $currentType = strtolower(old('type', $leave->type ?? 'annual'));
            @endphp
            <select id="type" name="type">
                <option value="annual"{{ $currentType === 'annual' ? ' selected' : '' }}>Annual</option>
                <option value="sick"{{ $currentType === 'sick' ? ' selected' : '' }}>Sick</option>
                <option value="casual"{{ $currentType === 'casual' ? ' selected' : '' }}>Casual</option>
                <option value="unpaid"{{ $currentType === 'unpaid' ? ' selected' : '' }}>Unpaid</option>
                <option value="other"{{ $currentType === 'other' ? ' selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="full-width">
            <label for="reason">Reason</label>
            <input id="reason" name="reason" value="{{ old('reason', $leave->reason ?? '') }}">
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = strtolower(old('status', $leave->status ?? 'pending'));
            @endphp
            <select id="status" name="status">
                <option value="pending"{{ $currentStatus === 'pending' ? ' selected' : '' }}>Pending</option>
                <option value="approved"{{ $currentStatus === 'approved' ? ' selected' : '' }}>Approved</option>
                <option value="rejected"{{ $currentStatus === 'rejected' ? ' selected' : '' }}>Rejected</option>
            </select>
        </div>
    </div>
</div>

