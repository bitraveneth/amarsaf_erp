@props(['notice' => null])

@if(!empty($notice) && auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accounts_officer']))
    <div class="print-hidden print:hidden mx-auto w-full max-w-(--breakpoint-2xl) px-4 pt-4 md:px-6 md:pt-6" role="status" aria-live="polite">
        <section class="rounded-2xl border border-warning-200 bg-warning-50 p-4 shadow-theme-sm dark:border-warning-500/30 dark:bg-warning-500/10 sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="erp-badge-warning">Ledger</p>
                        <h2 class="erp-h3 mt-2 text-gray-900 dark:text-white">{{ $notice['title'] }}</h2>
                        <p class="erp-caption mt-1 max-w-3xl">{{ $notice['why'] }}</p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ $notice['list_url'] }}" class="erp-btn-primary">Review expenses</a>
                    <a href="{{ $notice['report_url'] }}" class="erp-btn-secondary">Income statement</a>
                </div>
            </div>
        </section>
    </div>
@endif
