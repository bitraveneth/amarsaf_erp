@php
    /** @var \App\Models\PaymentInstrument|null $instrument */
    $instrument = $instrument ?? null;
    $kind = $kind ?? 'bank';
@endphp
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <div class="erp-field sm:col-span-2">
        <label class="erp-label">Display name <span class="text-error-500">*</span></label>
        <input name="name" value="{{ old('name', $instrument?->name) }}" class="erp-input" required
               placeholder="{{ $kind === 'mobile' ? 'e.g. bKash Merchant' : ($kind === 'cash' ? 'e.g. Petty cash – Factory' : 'e.g. BRAC Current – Gulshan') }}">
    </div>

    <div class="erp-field">
        <label class="erp-label">Account holder name</label>
        <input name="account_holder_name" value="{{ old('account_holder_name', $instrument?->account_holder_name) }}" class="erp-input"
               placeholder="Name on the account">
    </div>

    <div class="erp-field">
        <label class="erp-label">{{ $kind === 'mobile' ? 'Provider / bank name' : 'Bank name' }}</label>
        <input name="bank_name" value="{{ old('bank_name', $instrument?->bank_name) }}" class="erp-input"
               placeholder="{{ $kind === 'mobile' ? 'bKash / Nagad' : 'BRAC Bank Limited' }}">
    </div>

    <div class="erp-field">
        <label class="erp-label">{{ $kind === 'mobile' ? 'Wallet / mobile no.' : 'Account number' }}</label>
        <input name="account_number" value="{{ old('account_number', $instrument?->account_number) }}" class="erp-input"
               placeholder="{{ $kind === 'mobile' ? '01XXXXXXXXX' : '0123456789' }}">
    </div>

    <div class="erp-field">
        <label class="erp-label">Branch</label>
        <input name="branch" value="{{ old('branch', $instrument?->branch) }}" class="erp-input" placeholder="Gulshan">
    </div>

    <div class="erp-field">
        <label class="erp-label">Routing number</label>
        <input name="routing" value="{{ old('routing', $instrument?->routing) }}" class="erp-input">
    </div>

    <div class="erp-field">
        <label class="erp-label">SWIFT / BIC</label>
        <input name="swift_code" value="{{ old('swift_code', $instrument?->swift_code) }}" class="erp-input" placeholder="BRAKBDDH">
    </div>

    <div class="erp-field">
        <label class="erp-label">IBAN</label>
        <input name="iban" value="{{ old('iban', $instrument?->iban) }}" class="erp-input">
    </div>

    <div class="erp-field">
        <label class="erp-label">Phone</label>
        <input name="phone" value="{{ old('phone', $instrument?->phone) }}" class="erp-input" placeholder="+880…">
    </div>

    <div class="erp-field sm:col-span-2">
        <label class="erp-label">Bank / branch address</label>
        <input name="address" value="{{ old('address', $instrument?->address) }}" class="erp-input"
               placeholder="Branch address for statements and print">
    </div>

    <div class="erp-field sm:col-span-2">
        <label class="erp-label">Notes</label>
        <textarea name="details" rows="2" class="erp-input" maxlength="255" placeholder="Internal notes for accounts staff">{{ old('details', $instrument?->details) }}</textarea>
    </div>
</div>
