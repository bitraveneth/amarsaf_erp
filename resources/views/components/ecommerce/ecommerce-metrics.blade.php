@props([
    'agentCount' => 0,
    'totalOrderCount' => 0,
    'returnOrderCount' => 0,
])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 md:gap-6">
    {{-- Agents --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div
            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            {{-- people icon --}}
            <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M8.804 5.602a2.197 2.197 0 1 0 0 4.394 2.197 2.197 0 0 0 0-4.394ZM5.107 7.799a3.697 3.697 0 1 1 7.394 0 3.697 3.697 0 0 1-7.394 0Zm-.245 7.522C4.087 16.088 3.703 17.061 3.516 17.861c-.033.142.004.256.09.35.095.103.26.188.47.188h9.349c.209 0 .374-.085.468-.188.086-.094.124-.208.09-.35-.186-.8-.57-1.773-1.345-2.541-.756-.749-1.948-1.366-3.888-1.366-1.94 0-3.132.617-3.888 1.366Z"
                    fill="" />
            </svg>
        </div>

        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Agents</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                    {{ number_format($agentCount ?? 0) }}
                </h4>
            </div>

            <span
                class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                Active selling partners
            </span>
        </div>
    </div>

    {{-- Orders --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div
            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            {{-- box / order icon --}}
            <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M11.665 3.756a.75.75 0 0 1 .671 0l6.446 3.223-6.446 3.223a.75.75 0 0 1-.672 0L5.22 6.979l6.445-3.223Zm-7.629 4.436V16.095c0 .284.16.544.414.671l6.542 3.271V11.65a2.6 2.6 0 0 1-.256-.108l-6.7-3.35Zm8.714 12.282 6.543-3.272a.75.75 0 0 0 .413-.671V8.192l-6.7 3.35a2.6 2.6 0 0 1-.256.108v8.824Z"
                    fill="" />
            </svg>
        </div>

        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Sales orders</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                    {{ number_format($totalOrderCount ?? 0) }}
                </h4>
            </div>

            <span
                class="flex items-center gap-1 rounded-full bg-brand-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                Excluding customer returns
            </span>
        </div>
    </div>

    {{-- Returns --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div
            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            {{-- arrow U-turn icon --}}
            <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M9.53 4.47a.75.75 0 0 1 0 1.06L7.81 7.25H13a5.75 5.75 0 0 1 0 11.5h-3a.75.75 0 0 1 0-1.5h3a4.25 4.25 0 0 0 0-8.5H7.81l1.72 1.72a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0Z"
                    fill="" />
            </svg>
        </div>

        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Returns</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                    {{ number_format($returnOrderCount ?? 0) }}
                </h4>
            </div>

            <span
                class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-400">
                Customer return orders
            </span>
        </div>
    </div>
</div>
