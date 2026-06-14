<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Carrier name <span class="text-error-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $carrier->name) }}" required
               placeholder="e.g. ABC Transport, Express Courier Ltd"
               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('name')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $carrier->contact_person) }}"
               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $carrier->phone) }}"
               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $carrier->email) }}"
               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('email')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tax ID / BIN</label>
        <input type="text" name="tax_id" value="{{ old('tax_id', $carrier->tax_id) }}"
               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('tax_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
        <textarea name="address" rows="2"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('address', $carrier->address) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <textarea name="notes" rows="2"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes', $carrier->notes) }}</textarea>
    </div>
    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $carrier->is_active ?? true))
                   class="h-4 w-4 rounded border-gray-300 text-brand-500 dark:border-gray-700 dark:bg-gray-800">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
        </label>
    </div>
</div>
