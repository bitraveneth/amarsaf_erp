@php
    $debit = $line['debit'] ?? '';
    $credit = $line['credit'] ?? '';
    $blankAmount = static fn ($amount) => $amount === null || $amount === '' || (float) $amount == 0.0;
@endphp
<tr data-journal-line>
    <td class="is-right text-gray-400" data-journal-line-no>{{ is_numeric($index) ? ((int) $index + 1) : '' }}</td>
    <td>
        <select name="lines[{{ $index }}][account_id]" class="erp-select" data-journal-account>
            <option value="">Select account</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) ($line['account_id'] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" step="0.01" min="0" inputmode="decimal" name="lines[{{ $index }}][debit]" value="{{ $blankAmount($debit) ? '' : $debit }}" class="erp-input text-right tabular-nums" placeholder="0.00" data-journal-debit-input>
    </td>
    <td>
        <input type="number" step="0.01" min="0" inputmode="decimal" name="lines[{{ $index }}][credit]" value="{{ $blankAmount($credit) ? '' : $credit }}" class="erp-input text-right tabular-nums" placeholder="0.00" data-journal-credit-input>
    </td>
    <td>
        <input type="text" name="lines[{{ $index }}][description]" value="{{ $line['description'] ?? '' }}" class="erp-input" placeholder="Optional note" maxlength="255">
    </td>
    <td class="is-right">
        <button type="button" class="erp-btn-danger" data-journal-remove>Remove</button>
    </td>
</tr>
