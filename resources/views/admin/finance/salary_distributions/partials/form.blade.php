<div class="form-section">
    <div class="section-header">
        <h3>Employee &amp; period</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">Select employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (string)old('employee_id', $distribution->employee_id ?? '') === (string)$employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="period_start">Period start</label>
            <input id="period_start" name="period_start" type="date"
                   value="{{ old('period_start', optional($distribution->period_start)->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d')) }}"
                   required>
        </div>
        <div>
            <label for="period_end">Period end</label>
            <input id="period_end" name="period_end" type="date"
                   value="{{ old('period_end', optional($distribution->period_end)->format('Y-m-d') ?? now()->endOfMonth()->format('Y-m-d')) }}"
                   required>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Salary breakdown</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="base_salary">Base salary</label>
            <input id="base_salary" name="base_salary" type="number" step="0.01"
                   value="{{ old('base_salary', $distribution->base_salary ?? 0) }}" required>
        </div>
        <div>
            <label for="bonus">Bonus</label>
            <input id="bonus" name="bonus" type="number" step="0.01"
                   value="{{ old('bonus', $distribution->bonus ?? 0) }}">
        </div>
        <div>
            <label for="ta_allowances">TA allowances</label>
            <input id="ta_allowances" name="ta_allowances" type="number" step="0.01"
                   value="{{ old('ta_allowances', $distribution->ta_allowances ?? 0) }}">
        </div>
        <div>
            <label for="da_allowances">DA allowances</label>
            <input id="da_allowances" name="da_allowances" type="number" step="0.01"
                   value="{{ old('da_allowances', $distribution->da_allowances ?? 0) }}">
        </div>
        <div>
            <label for="commission">Commission</label>
            <input id="commission" name="commission" type="number" step="0.01"
                   value="{{ old('commission', $distribution->commission ?? 0) }}">
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Payment &amp; documents</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="payment_method">Payment method</label>
            @php
                $currentMethod = old('payment_method', $distribution->payment_method ?? 'bank');
            @endphp
            <select id="payment_method" name="payment_method">
                <option value="">Select method</option>
                <option value="bank"{{ $currentMethod === 'bank' ? ' selected' : '' }}>Bank</option>
                <option value="cash"{{ $currentMethod === 'cash' ? ' selected' : '' }}>Cash</option>
                <option value="cheque"{{ $currentMethod === 'cheque' ? ' selected' : '' }}>Cheque</option>
                <option value="online"{{ $currentMethod === 'online' ? ' selected' : '' }}>Online payment</option>
            </select>
        </div>
        <div>
            <label for="document">Document</label>
            <input id="document" name="document" type="file">
            @if(!empty($distribution->document_path))
                <div class="preview">
                    <small>Current:</small>
                    <a href="{{ asset('storage/'.$distribution->document_path) }}" target="_blank" class="button-secondary">View document</a>
                </div>
            @endif
        </div>
        <div class="full-width">
            <label for="remarks">Remarks</label>
            <input id="remarks" name="remarks" value="{{ old('remarks', $distribution->remarks ?? '') }}">
        </div>
    </div>
</div>

