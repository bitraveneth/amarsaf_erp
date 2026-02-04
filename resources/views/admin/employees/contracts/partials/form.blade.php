<div class="form-section">
    <div class="section-header">
        <h3>Basic details</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="reference">Contract reference</label>
            <input id="reference" name="reference" value="{{ old('reference', $contract->reference ?? '') }}" required>
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                @php
                    $currentStatus = old('status', $contract->status ?? 'active');
                @endphp
                <option value="active"{{ $currentStatus === 'active' ? ' selected' : '' }}>Active</option>
                <option value="on_hold"{{ $currentStatus === 'on_hold' ? ' selected' : '' }}>On hold</option>
                <option value="ended"{{ $currentStatus === 'ended' ? ' selected' : '' }}>Ended</option>
            </select>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Period & schedule</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="start_date">Start date</label>
            @php
                $startDefault = $contract->start_date ? $contract->start_date->format('Y-m-d') : now()->format('Y-m-d');
            @endphp
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $startDefault) }}" required>
        </div>
        <div>
            <label for="end_date">End date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}">
            <small class="text-muted">Leave blank for open-ended contracts.</small>
        </div>
        <div class="full-width">
            <label for="working_schedule">Working schedule</label>
            <input id="working_schedule" name="working_schedule" list="working-schedules" value="{{ old('working_schedule', $contract->working_schedule ?? '') }}">
            @isset($workingSchedules)
                <datalist id="working-schedules">
                    @foreach($workingSchedules as $schedule)
                        <option value="{{ $schedule }}"></option>
                    @endforeach
                </datalist>
            @endisset
            <small class="text-muted">Select an existing schedule or type a new one, e.g. Mon–Sat, 9:00–17:00.</small>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Salary & allowances</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="salary_amount">Salary amount</label>
            <input id="salary_amount" name="salary_amount" type="number" step="0.01" value="{{ old('salary_amount', $contract->salary_amount ?? '') }}">
        </div>
        <div>
            <label for="travel_allowance">Travel allowance (TA)</label>
            <input id="travel_allowance" name="travel_allowance" type="number" step="0.01" value="{{ old('travel_allowance', $contract->travel_allowance ?? '') }}">
        </div>
        <div>
            <label for="dearness_allowance">Dearness allowance (DA)</label>
            <input id="dearness_allowance" name="dearness_allowance" type="number" step="0.01" value="{{ old('dearness_allowance', $contract->dearness_allowance ?? '') }}">
        </div>
        <div>
            <label for="bonus">Bonus</label>
            <input id="bonus" name="bonus" type="number" step="0.01" value="{{ old('bonus', $contract->bonus ?? '') }}">
        </div>
    </div>
</div>
