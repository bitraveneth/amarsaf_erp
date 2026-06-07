@props([
    'documentTypes',
    'existingDocuments' => [],
])

@php
    use App\Support\AgentKycDocuments;

    $existingDocuments = AgentKycDocuments::normalizeList($existingDocuments);
    $maxSizeLabel = AgentKycDocuments::maxFileSizeLabel();
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
@endphp

<div class="sm:col-span-2 lg:col-span-3 space-y-4" x-data="kycDocumentsField()">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">KYC Documents</h4>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Upload compliance documents by type. Max {{ $maxSizeLabel }} per file (PDF, JPG, PNG).
            </p>
        </div>
        <a href="{{ route('admin.kyc-document-types.index') }}"
           class="inline-flex shrink-0 items-center text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
            Manage document types
        </a>
    </div>

    @if(! empty($existingDocuments))
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-800/50">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Uploaded documents</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($existingDocuments as $doc)
                    <label class="flex cursor-pointer items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <input type="checkbox"
                               name="kyc_keep[]"
                               value="{{ $doc['uid'] }}"
                               checked
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                    {{ $doc['type_name'] }}
                                </span>
                                @if($doc['size'])
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ AgentKycDocuments::formatBytes($doc['size']) }}</span>
                                @endif
                            </div>
                            @if(! empty($doc['path']))
                                <a href="{{ asset('storage/'.$doc['path']) }}" target="_blank"
                                   class="mt-1 inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                   onclick="event.stopPropagation()">
                                    {{ $doc['original_name'] ?? basename($doc['path']) }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Reference only — upload a file below to attach.</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            <p class="border-t border-gray-100 px-4 py-2 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                Uncheck a document to remove it when you save.
            </p>
        </div>
    @endif

    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-900/40">
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Add documents</p>
            <button type="button"
                    @click="addRow()"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                + Add row
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(row, index) in rows" :key="row.key">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-12 lg:items-end">
                        <div class="lg:col-span-3">
                            <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">Document type</label>
                            <select class="{{ $inputClass }}"
                                    x-model="row.typeId"
                                    :name="`kyc_new[${index}][type_id]`">
                                <option value="">Select type</option>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                                <option value="__custom__">Other (custom)</option>
                            </select>
                        </div>

                        <div class="lg:col-span-3" x-show="row.typeId === '__custom__'" x-cloak>
                            <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">Custom type name</label>
                            <input type="text"
                                   x-model="row.customType"
                                   :name="`kyc_new[${index}][custom_type]`"
                                   placeholder="e.g. VAT Registration"
                                   class="{{ $inputClass }}">
                        </div>

                        <div class="lg:col-span-5" :class="row.typeId === '__custom__' ? '' : 'lg:col-span-6'">
                            <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">File (max {{ $maxSizeLabel }})</label>
                            <input type="file"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   :name="`kyc_new[${index}][file]`"
                                   class="{{ $inputClass }} file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-gray-700 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>

                        <div class="flex lg:col-span-1 lg:justify-end">
                            <button type="button"
                                    @click="removeRow(index)"
                                    x-show="rows.length > 1"
                                    class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @error('kyc_new.*')
        <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
    @error('kyc_new.*.type_id')
        <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
    @error('kyc_new.*.custom_type')
        <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
    @error('kyc_new.*.file')
        <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('kycDocumentsField', () => ({
                    rows: [{ key: Date.now(), typeId: '', customType: '' }],
                    addRow() {
                        this.rows.push({ key: Date.now() + Math.random(), typeId: '', customType: '' });
                    },
                    removeRow(index) {
                        if (this.rows.length <= 1) {
                            return;
                        }
                        this.rows.splice(index, 1);
                    },
                }));
            });
        </script>
    @endpush
@endonce
