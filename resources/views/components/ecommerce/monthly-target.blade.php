@props([
    'outstandingReceivables' => 0,
    'monthlyReceipts' => [],
    'todayReceipts' => 0,
])

<div class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="shadow-default rounded-2xl bg-white px-5 pb-11 pt-5 dark:bg-gray-900 sm:px-6 sm:pt-6">
        <div class="flex justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Monthly Target
                </h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    Collections vs outstanding for this year
                </p>
            </div>
            <!-- Dropdown Menu -->
            <x-common.dropdown-menu />
            <!-- End Dropdown Menu -->
        </div>

        @php
            $annualTarget    = max(($outstandingReceivables ?? 0) + array_sum($monthlyReceipts ?? []), 1);
            $collectedSoFar  = array_sum($monthlyReceipts ?? []);
            $collectionRate  = round(($collectedSoFar / $annualTarget) * 100);
            $collectionDelta = $todayReceipts > 0 ? '+10%' : '+0%'; // simple demo delta
        @endphp

        <div class="relative max-h-[195px]">
            {{-- Radial gauge chart --}}
            <div id="chartTwo" class="h-full"></div>
            <span class="absolute left-1/2 top-[85%] -translate-x-1/2 -translate-y-[85%] rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                {{ $collectionDelta }}
            </span>
        </div>
        <p class="mx-auto mt-1.5 w-full max-w-[380px] text-center text-sm text-gray-500 sm:text-base">
            You collected BDT {{ number_format($todayReceipts ?? 0, 2) }} today.
            Overall collection progress for {{ now()->year }} is {{ $collectionRate }}%.
        </p>
    </div>

    <div class="flex items-center justify-center gap-5 px-6 py-3.5 sm:gap-8 sm:py-5">
        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Target
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                BDT {{ number_format($annualTarget, 2) }}
            </p>
        </div>

        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>

        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Revenue
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                BDT {{ number_format($collectedSoFar, 2) }}
            </p>
        </div>

        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>

        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Today
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                BDT {{ number_format($todayReceipts ?? 0, 2) }}
            </p>
        </div>
    </div>
</div>
