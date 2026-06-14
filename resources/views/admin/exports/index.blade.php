@extends('layouts.app')

@section('content')
@php
    $dateFilteredCount = collect($exportGroups)
        ->flatten(1)
        ->where('supports_date_range', true)
        ->count() + count($specialExports ?? []);
    $exportBaseUrl = url('/admin/exports');
    $tallyPreviewBase = url('/admin/exports/tally/preview');
    $tallyDownloadBase = url('/admin/exports/tally/download');
    $monthEndDownloadBase = url('/admin/exports/month-end-pack/download');
@endphp

<div
    class="export-center space-y-6"
    x-data="{
        query: '',
        selectedModule: @js($selectedModule ?? ''),
        mode: @js($rangeMode),
        selectedPreset: @js($rangeMode === 'preset' ? $selectedRange : 'month'),
        from: @js($fromValue),
        to: @js($toValue),
        fromPicker: null,
        toPicker: null,
        exportBase: @js($exportBaseUrl),
        tallyPreviewBase: @js($tallyPreviewBase),
        tallyDownloadBase: @js($tallyDownloadBase),
        monthEndDownloadBase: @js($monthEndDownloadBase),
        previewLoading: false,
        previewError: null,
        previewData: null,
        previewKind: null,
        expandedVoucher: null,
        matches(text) {
            if (! this.query.trim()) return true;
            return text.toLowerCase().includes(this.query.trim().toLowerCase());
        },
        initPickers() {
            if (this.mode !== 'custom' || ! window.flatpickr) return;

            this.destroyPickers();

            if (! this.$refs.fromCalendar || ! this.$refs.toCalendar) return;

            this.fromPicker = window.flatpickr(this.$refs.fromCalendar, {
                inline: true,
                dateFormat: 'Y-m-d',
                defaultDate: this.from || null,
                maxDate: this.to || 'today',
                disableMobile: true,
                onReady: (_dates, _str, instance) => {
                    instance.calendarContainer?.classList.add('export-center-flatpickr');
                },
                onChange: (_dates, value) => {
                    this.from = value;
                    if (this.toPicker) {
                        this.toPicker.set('minDate', value || null);
                    }
                },
            });

            this.toPicker = window.flatpickr(this.$refs.toCalendar, {
                inline: true,
                dateFormat: 'Y-m-d',
                defaultDate: this.to || null,
                minDate: this.from || null,
                maxDate: 'today',
                disableMobile: true,
                onReady: (_dates, _str, instance) => {
                    instance.calendarContainer?.classList.add('export-center-flatpickr');
                },
                onChange: (_dates, value) => {
                    this.to = value;
                    if (this.fromPicker) {
                        this.fromPicker.set('maxDate', value || 'today');
                    }
                },
            });
        },
        destroyPickers() {
            this.fromPicker?.destroy();
            this.toPicker?.destroy();
            this.fromPicker = null;
            this.toPicker = null;
        },
        selectPreset(key) {
            this.mode = 'preset';
            this.selectedPreset = key;
            this.$nextTick(() => this.$refs.rangeForm.requestSubmit());
        },
        dateQuery() {
            const params = new URLSearchParams();

            if (this.mode === 'all') {
                params.set('range', 'all');
            } else if (this.mode === 'preset') {
                params.set('range', this.selectedPreset);
            } else {
                if (this.from) params.set('from', this.from);
                if (this.to) params.set('to', this.to);
            }

            return params.toString();
        },
        buildQuery() {
            const params = new URLSearchParams(this.dateQuery());

            if (this.selectedModule) {
                params.set('module', this.selectedModule);
            }

            return params.toString();
        },
        exportUrl(format) {
            if (! this.selectedModule || this.isSpecialExport()) return '#';

            const query = this.dateQuery();
            return `${this.exportBase}/${this.selectedModule}/${format}${query ? `?${query}` : ''}`;
        },
        previewUrl() {
            if (! this.selectedModule) return '#';

            const query = this.dateQuery();

            if (this.isTallyExport()) {
                return `${this.tallyPreviewBase}${query ? `?${query}` : ''}`;
            }

            return `${this.exportBase}/${this.selectedModule}/preview${query ? `?${query}` : ''}`;
        },
        downloadUrl() {
            if (! this.selectedModule) return '#';

            const query = this.dateQuery();

            if (this.isTallyExport()) {
                return `${this.tallyDownloadBase}${query ? `?${query}` : ''}`;
            }

            if (this.isMonthEndPack()) {
                return `${this.monthEndDownloadBase}${query ? `?${query}` : ''}`;
            }

            return '#';
        },
        isSpecialExport() {
            return this.isTallyExport() || this.isMonthEndPack();
        },
        isTallyExport() {
            return this.selectedModule === 'tally-xml';
        },
        isMonthEndPack() {
            return this.selectedModule === 'month-end-pack';
        },
        rowClass(index) {
            const style = this.previewData?.row_styles?.[index];
            return style ? `is-${style}` : '';
        },
        isNumericColumn(index) {
            return (this.previewData?.numeric_columns ?? []).includes(index);
        },
        async loadPreview() {
            if (! this.selectedModule || this.isMonthEndPack()) {
                this.previewData = null;
                this.previewKind = this.isMonthEndPack() ? 'pack' : null;
                this.previewError = null;
                return;
            }

            this.previewLoading = true;
            this.previewError = null;
            this.expandedVoucher = null;

            try {
                const response = await fetch(this.previewUrl(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('Preview failed');
                }

                const payload = await response.json();
                this.previewData = payload;
                this.previewKind = payload.vouchers ? 'tally' : 'tabular';
            } catch (error) {
                this.previewError = 'Could not load preview. Check the date range and try again.';
                this.previewData = null;
                this.previewKind = null;
            } finally {
                this.previewLoading = false;
            }
        },
        formatMoney(value) {
            const amount = Number(value ?? 0);
            return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        toggleVoucher(index) {
            this.expandedVoucher = this.expandedVoucher === index ? null : index;
        },
        formatDisplayDate(value) {
            if (! value) return 'Pick a date on the calendar';

            const parts = value.split('-');
            if (parts.length !== 3) return value;

            const date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            return date.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
        },
        selectModule(slug) {
            this.selectedModule = slug;
        },
    }"
    x-init="
        $watch('mode', (value) => {
            if (value === 'custom') {
                destroyPickers();
                $nextTick(() => {
                    setTimeout(() => initPickers(), 60);
                });
            } else {
                destroyPickers();
            }
        });
        if (mode === 'custom') {
            $nextTick(() => {
                setTimeout(() => initPickers(), 60);
            });
        }
        if (selectedModule) {
            $nextTick(() => loadPreview());
        }
        $watch('selectedModule', (value) => {
            if (value) {
                loadPreview();
            } else {
                previewData = null;
                previewKind = null;
                previewError = null;
            }
        });
    "
>
    {{-- Hero --}}
    <section class="export-center-hero">
        <div class="export-center-hero__glow" aria-hidden="true"></div>
        <div class="export-center-hero__inner">
            <div class="export-center-hero__copy">
                <p class="erp-eyebrow text-brand-600 dark:text-brand-400">Reports & analytics</p>
                <h1 class="erp-dash-h1 mt-2">Export center</h1>
                <p class="erp-body mt-2 max-w-2xl">
                    Preview and download financial statements, aging, sales register, open balances, month-end ZIP packs, or Tally XML for your CA.
                </p>
            </div>
            <div class="export-center-hero__stats">
                <div class="export-center-stat">
                    <p class="export-center-stat__label">Modules</p>
                    <x-admin.metric-value :value="(string) $moduleCount" class="!mt-1" />
                </div>
                <div class="export-center-stat">
                    <p class="export-center-stat__label">Date-aware</p>
                    <x-admin.metric-value :value="(string) $dateFilteredCount" class="!mt-1" />
                </div>
                <div class="export-center-stat export-center-stat--wide">
                    <p class="export-center-stat__label">Active period</p>
                    <p class="erp-body-strong mt-1">{{ $periodLabel }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Export builder --}}
    <section class="export-center-panel">
        <div class="export-center-panel__head">
            <div>
                <h2 class="erp-h2">Export file</h2>
                <p class="erp-caption mt-1">Select module, preview rows, set time range — then download.</p>
            </div>
        </div>

        <form
            method="GET"
            action="{{ route('admin.export-center') }}"
            class="export-center-builder"
            x-ref="rangeForm"
        >
            <div class="export-center-builder__left">
                <div class="export-center-builder__module">
                    <label class="flex flex-col gap-1.5">
                        <span class="erp-label">Module</span>
                        <select
                            name="module"
                            x-model="selectedModule"
                            class="erp-input h-12"
                        >
                            <option value="">Choose a module…</option>
                            @foreach($exportGroups as $category => $modules)
                                <optgroup label="{{ $category }}">
                                    @foreach($modules as $module)
                                        <option
                                            value="{{ $module['slug'] }}"
                                            @selected(($selectedModule ?? '') === $module['slug'])
                                        >
                                            {{ $module['title'] }}
                                            @if($module['supports_date_range'])
                                                (date filter)
                                            @else
                                                (full list)
                                            @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            @if(count($specialExports ?? []) > 0)
                                <optgroup label="Accounting integrations">
                                    @foreach($specialExports as $special)
                                        <option
                                            value="{{ $special['slug'] }}"
                                            @selected(request('module') === $special['slug'])
                                        >
                                            {{ $special['title'] }} (XML · date filter)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </label>

                    <div class="export-center-download-actions">
                        <button
                            type="button"
                            class="export-center-download export-center-download--preview"
                            @click="loadPreview()"
                            x-bind:disabled="! selectedModule || previewLoading"
                            x-bind:class="{ 'opacity-40': ! selectedModule || previewLoading }"
                        >
                            <span x-show="! previewLoading">Preview</span>
                            <span x-show="previewLoading" x-cloak>Loading…</span>
                        </button>
                        <template x-if="! isSpecialExport()">
                            <a
                                href="#"
                                class="export-center-download export-center-download--csv"
                                x-bind:href="exportUrl('csv')"
                                x-bind:class="{ 'pointer-events-none opacity-40': ! selectedModule }"
                            >
                                Download CSV
                            </a>
                        </template>
                        <template x-if="! isSpecialExport()">
                            <a
                                href="#"
                                class="export-center-download export-center-download--pdf"
                                x-bind:href="exportUrl('pdf')"
                                x-bind:class="{ 'pointer-events-none opacity-40': ! selectedModule }"
                            >
                                Download PDF
                            </a>
                        </template>
                        <template x-if="isMonthEndPack()">
                            <a
                                href="#"
                                class="export-center-download export-center-download--csv"
                                x-bind:href="downloadUrl()"
                                x-bind:class="{ 'pointer-events-none opacity-40': ! selectedModule }"
                            >
                                Download ZIP pack
                            </a>
                        </template>
                        <template x-if="isTallyExport()">
                            <a
                                href="#"
                                class="export-center-download export-center-download--xml"
                                x-bind:href="downloadUrl()"
                                x-bind:class="{ 'pointer-events-none opacity-40': ! selectedModule }"
                            >
                                Download Tally XML
                            </a>
                        </template>
                    </div>
                </div>

                @if(count($featuredModules) > 0)
                    <div class="export-center-builder__featured">
                        <p class="erp-label">Quick pick</p>
                        <p class="erp-caption mt-1">Most used and important modules</p>
                        <div class="export-center-featured-grid">
                            @foreach($featuredModules as $module)
                                <button
                                    type="button"
                                    class="export-center-featured-card"
                                    x-bind:class="{ 'is-active': selectedModule === @js($module['slug']) }"
                                    @click="selectModule(@js($module['slug']))"
                                >
                                    <span class="export-center-featured-card__badge">{{ $module['badge'] }}</span>
                                    <span class="export-center-featured-card__title">{{ $module['title'] }}</span>
                                    <span class="export-center-featured-card__hint">{{ $module['hint'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="export-center-builder__range">
                    <div>
                        <h3 class="erp-h3">Time range</h3>
                        <p class="erp-caption mt-0.5">Transactional modules only · master lists export in full</p>
                    </div>

                    <div class="export-center-mode">
                        <label class="export-center-mode__option">
                            <input type="radio" value="all" x-model="mode" class="sr-only">
                            <span class="export-center-mode__card" x-bind:class="{ 'is-active': mode === 'all' }">
                                <span class="export-center-mode__title">All records</span>
                                <span class="export-center-mode__desc">No date filter</span>
                            </span>
                        </label>
                        <label class="export-center-mode__option">
                            <input type="radio" value="preset" x-model="mode" class="sr-only">
                            <span class="export-center-mode__card" x-bind:class="{ 'is-active': mode === 'preset' }">
                                <span class="export-center-mode__title">Quick period</span>
                                <span class="export-center-mode__desc">Today, week, month, and more</span>
                            </span>
                        </label>
                        <label class="export-center-mode__option">
                            <input type="radio" value="custom" x-model="mode" class="sr-only">
                            <span class="export-center-mode__card" x-bind:class="{ 'is-active': mode === 'custom' }">
                                <span class="export-center-mode__title">Custom dates</span>
                                <span class="export-center-mode__desc">Pick from and to on the calendar</span>
                            </span>
                        </label>
                    </div>

                    <div x-show="mode === 'preset'" x-cloak class="export-center-calendar__quick">
                        <p class="export-center-calendar__quick-label">Quick fill</p>
                        <div class="export-center-quick-chips">
                            @foreach($rangePresets as $key => $preset)
                                @if($key === 'all')
                                    @continue
                                @endif
                                <button
                                    type="button"
                                    class="export-center-quick-chip"
                                    x-bind:class="{ 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300': mode === 'preset' && selectedPreset === @js($key) }"
                                    @click="selectPreset(@js($key))"
                                >
                                    {{ $preset['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="mode === 'custom'" x-cloak class="export-center-calendar-range">
                        <div class="export-center-calendar-range__head">
                            <div class="export-center-calendar-range__title-wrap">
                                <span class="export-center-calendar-range__icon" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="export-center-calendar-range__title">Calendar range</p>
                                    <p class="export-center-calendar-range__desc">Pick start and end dates below</p>
                                </div>
                            </div>
                        </div>

                        <div class="export-center-calendar-range__summary">
                            <div class="export-center-date-pill">
                                <span class="export-center-date-pill__label">From</span>
                                <span class="export-center-date-pill__value" x-text="formatDisplayDate(from)"></span>
                            </div>
                            <span class="export-center-date-pill__arrow" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <div class="export-center-date-pill">
                                <span class="export-center-date-pill__label">To</span>
                                <span class="export-center-date-pill__value" x-text="formatDisplayDate(to)"></span>
                            </div>
                        </div>

                        <div class="export-center-custom-range">
                            <div class="export-center-custom-range__panel">
                                <div x-ref="fromCalendar" class="export-center-inline-calendar"></div>
                            </div>
                            <div class="export-center-custom-range__panel">
                                <div x-ref="toCalendar" class="export-center-inline-calendar"></div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="range" value="all" x-bind:disabled="mode !== 'all'">
                    <input type="hidden" name="range" x-bind:value="selectedPreset" x-bind:disabled="mode !== 'preset'">
                    <input type="hidden" name="from" x-bind:value="from" x-bind:disabled="mode !== 'custom'">
                    <input type="hidden" name="to" x-bind:value="to" x-bind:disabled="mode !== 'custom'">

                    <div class="export-center-builder__footer">
                        <button type="submit" class="erp-btn-primary">
                            Apply time range
                        </button>
                        <a href="{{ route('admin.export-center') }}" class="erp-btn-secondary">
                            Reset
                        </a>
                        <p class="export-center-builder__active">
                            Active:
                            <span class="erp-body-strong">{{ $periodLabel }}</span>
                        </p>
                    </div>
                </div>
        </form>
    </section>

    <section class="export-center-panel export-center-preview" x-show="selectedModule" x-cloak>
        <div class="export-center-preview__head">
            <div>
                <h2 class="erp-h2">Preview</h2>
                <p class="erp-caption mt-1">Review exported rows before downloading.</p>
            </div>
            <div class="export-center-preview__actions">
                <template x-if="previewData && previewKind === 'tabular'">
                    <div class="export-center-preview__downloads">
                        <a
                            class="export-center-download export-center-download--csv"
                            x-bind:href="previewData.csv_url"
                        >
                            CSV
                        </a>
                        <a
                            class="export-center-download export-center-download--pdf"
                            x-bind:href="previewData.pdf_url"
                        >
                            PDF
                        </a>
                    </div>
                </template>
                <template x-if="isMonthEndPack()">
                    <a
                        class="export-center-download export-center-download--csv"
                        x-bind:href="downloadUrl()"
                    >
                        Download ZIP pack
                    </a>
                </template>
                <button type="button" class="erp-btn-secondary" @click="loadPreview()" x-bind:disabled="previewLoading || isMonthEndPack()">
                    Refresh preview
                </button>
            </div>
        </div>

        <div x-show="previewError" class="export-center-preview__error" x-text="previewError"></div>

        <div x-show="previewLoading" class="export-center-preview__loading">
            Loading preview…
        </div>

        <template x-if="isMonthEndPack()">
            <div class="export-center-preview__pack">
                <p class="erp-body-strong">Month-end export pack</p>
                <p class="erp-caption mt-1">
                    One ZIP with P&amp;L, balance sheet, cash flow, trial balance, general ledger, AR/AP aging, VAT, sales register, outstanding invoices &amp; bills.
                    Tally XML is included when you have accounting access.
                </p>
                <ul class="export-center-preview__pack-list">
                    <li>Profit &amp; loss, balance sheet, cash flow</li>
                    <li>Trial balance and general ledger</li>
                    <li>AR/AP aging and VAT detail</li>
                    <li>Sales register and open balances</li>
                </ul>
                <p class="erp-caption mt-3">
                    Active period: <span class="erp-body-strong">{{ $periodLabel }}</span>
                </p>
            </div>
        </template>

        <template x-if="previewData && previewKind === 'tabular'">
            <div>
                <div class="export-center-preview__meta">
                    <div class="export-center-preview__meta-top">
                        <div>
                            <p class="erp-body-strong" x-text="previewData.title"></p>
                            <p class="erp-caption mt-1">
                                Showing <span x-text="previewData.rows.length"></span> of <span x-text="previewData.total_rows"></span> rows
                                <span x-show="previewData.truncated"> (preview limited)</span>
                            </p>
                        </div>
                        <span class="export-center-preview__badge" x-text="previewData.total_rows + ' rows'"></span>
                    </div>
                    <div class="export-center-preview__kpi-grid" x-show="(previewData.highlights ?? []).length">
                        <template x-for="(item, index) in previewData.highlights ?? []" :key="index">
                            <div class="export-center-preview__kpi">
                                <p class="export-center-preview__kpi-label" x-text="item.label"></p>
                                <p class="export-center-preview__kpi-value" x-text="item.value"></p>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="export-center-preview__table-wrap">
                    <table class="export-center-preview__table">
                        <thead>
                            <tr>
                                <template x-for="(column, columnIndex) in previewData.columns" :key="column">
                                    <th
                                        x-text="column"
                                        x-bind:class="{ 'is-right': isNumericColumn(columnIndex) }"
                                    ></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, rowIndex) in previewData.rows" :key="rowIndex">
                                <tr x-bind:class="rowClass(rowIndex)">
                                    <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                        <td
                                            x-text="cell"
                                            x-bind:class="{ 'is-right': isNumericColumn(cellIndex) }"
                                        ></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <template x-if="previewData && previewKind === 'tally'">
            <div>
                <div class="export-center-preview__meta">
                    <p class="erp-body-strong" x-text="previewData.title"></p>
                    <p class="erp-caption mt-1" x-text="previewData.period?.label"></p>
                    <div class="export-center-preview__stats">
                        <span class="export-center-preview__stat"><strong x-text="previewData.summary?.voucher_count ?? 0"></strong> vouchers</span>
                        <span class="export-center-preview__stat"><strong x-text="previewData.summary?.line_count ?? 0"></strong> lines</span>
                        <span class="export-center-preview__stat">Debit <strong x-text="formatMoney(previewData.summary?.total_debit)"></strong></span>
                        <span class="export-center-preview__stat">Credit <strong x-text="formatMoney(previewData.summary?.total_credit)"></strong></span>
                    </div>
                    <p class="erp-caption mt-2">
                        Showing <span x-text="previewData.vouchers.length"></span> of <span x-text="previewData.total_rows"></span> vouchers
                        <span x-show="previewData.truncated"> (preview limited)</span>
                    </p>
                </div>
                <div class="export-center-preview__table-wrap">
                    <table class="export-center-preview__table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Number</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="is-right">Debit</th>
                                <th class="is-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(voucher, index) in previewData.vouchers" :key="voucher.number + index">
                                <tr class="export-center-preview__voucher-row" @click="toggleVoucher(index)">
                                    <td>
                                        <span class="export-center-preview__expand" x-text="expandedVoucher === index ? '−' : '+'"></span>
                                    </td>
                                    <td x-text="voucher.number"></td>
                                    <td x-text="voucher.date"></td>
                                    <td x-text="voucher.type"></td>
                                    <td x-text="voucher.description"></td>
                                    <td class="is-right" x-text="formatMoney(voucher.debit)"></td>
                                    <td class="is-right" x-text="formatMoney(voucher.credit)"></td>
                                </tr>
                                <template x-if="expandedVoucher === index">
                                    <tr>
                                        <td colspan="7" class="export-center-preview__lines-cell">
                                            <table class="export-center-preview__lines-table">
                                                <thead>
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="is-right">Debit</th>
                                                        <th class="is-right">Credit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(line, lineIndex) in voucher.lines" :key="lineIndex">
                                                        <tr>
                                                            <td x-text="line.account"></td>
                                                            <td class="is-right" x-text="line.debit > 0 ? formatMoney(line.debit) : '—'"></td>
                                                            <td class="is-right" x-text="line.credit > 0 ? formatMoney(line.credit) : '—'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div x-show="! previewLoading && ! previewError && ! previewData && selectedModule && ! isMonthEndPack()" class="export-center-preview__empty">
            Click Preview to load data for the selected module and date range.
        </div>
    </section>

    {{-- Search --}}
    <div class="export-center-panel export-center-panel--compact">
        <label for="export-search" class="erp-label">Browse all modules</label>
        <input
            id="export-search"
            type="search"
            x-model="query"
            placeholder="Search agents, orders, inventory, finance…"
            class="erp-input mt-1.5"
        >
    </div>

    @if($moduleCount === 0)
        <div class="export-center-panel erp-empty">
            <p class="erp-empty-title">No exports available</p>
            <p class="erp-empty-text">Your role does not include permission to export any modules yet.</p>
        </div>
    @else
        @foreach($exportGroups as $category => $modules)
            <section class="export-center-group">
                <div class="export-center-group__head">
                    <h2 class="erp-h2">{{ $category }}</h2>
                    <span class="export-center-group__count">{{ count($modules) }} modules</span>
                </div>

                <div class="export-center-grid">
                    @foreach($modules as $module)
                        <article
                            class="export-center-card"
                            x-show="matches(@js($module['title'])) || matches(@js($category))"
                            x-cloak
                        >
                            <div class="export-center-card__top">
                                <div class="export-center-card__icon" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                @if($module['supports_date_range'])
                                    <span class="export-center-card__badge export-center-card__badge--dated">Date filter</span>
                                @else
                                    <span class="export-center-card__badge">Full list</span>
                                @endif
                            </div>

                            <h3 class="erp-h3 mt-3">{{ $module['title'] }}</h3>
                            <p class="erp-caption mt-1">
                                {{ $module['column_count'] }} columns
                                @if($module['supports_date_range'])
                                    · {{ $periodLabel }}
                                @endif
                            </p>

                            <div class="export-center-card__actions">
                                <a href="{{ $module['csv_url'] }}" class="export-center-download export-center-download--csv">
                                    CSV
                                </a>
                                <a href="{{ $module['pdf_url'] }}" class="export-center-download export-center-download--pdf">
                                    PDF
                                </a>
                                <button
                                    type="button"
                                    class="export-center-link"
                                    @click="selectModule(@js($module['slug']))"
                                >
                                    Use in builder
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if(count($specialExports ?? []) > 0)
            <section class="export-center-group">
                <div class="export-center-group__head">
                    <h2 class="erp-h2">Accounting integrations</h2>
                    <span class="export-center-group__count">{{ count($specialExports) }} exports</span>
                </div>

                <div class="export-center-grid">
                    @foreach($specialExports as $special)
                        <article
                            class="export-center-card export-center-card--special"
                            x-show="matches(@js($special['title'])) || matches('tally') || matches('accounting')"
                            x-cloak
                        >
                            <div class="export-center-card__top">
                                <div class="export-center-card__icon" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                                    </svg>
                                </div>
                                <span class="export-center-card__badge export-center-card__badge--dated">Tally XML</span>
                            </div>

                            <h3 class="erp-h3 mt-3">{{ $special['title'] }}</h3>
                            <p class="erp-caption mt-1">{{ $special['description'] }}</p>

                            <div class="export-center-card__actions">
                                <a href="{{ $special['download_url'] }}" class="export-center-download export-center-download--xml">
                                    XML
                                </a>
                                <button
                                    type="button"
                                    class="export-center-link"
                                    @click="selectModule(@js($special['slug'])); $el.closest('.export-center')?.querySelector('.export-center-preview')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                                >
                                    Preview in builder
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
