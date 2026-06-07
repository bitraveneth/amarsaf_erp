@php
    $supplier = $supplier ?? null;
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="{{ $labelClass }}">Name <span class="text-error-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}" required
               class="{{ $fieldClass }}" placeholder="e.g., ABC Corporation Ltd.">
        @error('name')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contact_person" class="{{ $labelClass }}">Contact person</label>
        <input type="text" id="contact_person" name="contact_person"
               value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
               class="{{ $fieldClass }}" placeholder="e.g., Md. Rahim Uddin">
        @error('contact_person')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email</label>
        <input type="email" id="email" name="email"
               value="{{ old('email', $supplier->email ?? '') }}"
               class="{{ $fieldClass }}" placeholder="contact@supplier.com">
        @error('email')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">Phone</label>
        <input type="text" id="phone" name="phone"
               value="{{ old('phone', $supplier->phone ?? '') }}"
               class="{{ $fieldClass }}" placeholder="+880 1XXX-XXXXXX">
        @error('phone')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tax_id" class="{{ $labelClass }}">Tax ID / BIN</label>
        <input type="text" id="tax_id" name="tax_id"
               value="{{ old('tax_id', $supplier->tax_id ?? '') }}"
               class="{{ $fieldClass }}" placeholder="e.g., 123456789012">
        @error('tax_id')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="{{ $labelClass }}">Address</label>
        <textarea id="address" name="address" rows="3" class="{{ $fieldClass }}"
                  placeholder="Street address, city, postal code">{{ old('address', $supplier->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>
</div>
