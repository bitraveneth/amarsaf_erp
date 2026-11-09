@props([
    'title',
    'lead' => null,
    'kicker' => 'Quick guide',
    'steps' => [],
    'examples' => [],
    'note' => null,
    'open' => true,
])

<details {{ $attributes->class(['erp-quick-guide mt-6']) }} @if($open) open @endif>
    <summary class="erp-quick-guide__summary">
        <span>
            <span class="erp-quick-guide__kicker">{{ $kicker }}</span>
            <span class="erp-quick-guide__title">{{ $title }}</span>
        </span>
        <span class="erp-quick-guide__chevron" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </span>
    </summary>

    <div class="erp-quick-guide__body">
        @if($lead)
            <p class="erp-quick-guide__lead">{{ $lead }}</p>
        @endif

        @if($steps !== [])
            <ol class="erp-quick-guide__steps">
                @foreach($steps as $index => $step)
                    <li>
                        <span class="erp-quick-guide__step-num">{{ $index + 1 }}</span>
                        <div>
                            <p class="erp-quick-guide__step-title">{{ $step['title'] ?? '' }}</p>
                            @if(! empty($step['body']))
                                <p class="erp-caption">{{ $step['body'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if($examples !== [])
            <div class="erp-quick-guide__examples">
                @foreach($examples as $example)
                    <article class="erp-quick-guide__example">
                        <header>
                            @if(! empty($example['eyebrow']))
                                <p class="erp-eyebrow">{{ $example['eyebrow'] }}</p>
                            @endif
                            <h3 class="erp-quick-guide__example-title">{{ $example['title'] ?? '' }}</h3>
                            @if(! empty($example['caption']))
                                <p class="erp-caption">{{ $example['caption'] }}</p>
                            @endif
                        </header>
                        @if(! empty($example['rows']))
                            <div class="erp-table-wrap">
                                <table class="erp-table">
                                    <thead>
                                        <tr>
                                            @foreach(($example['headers'] ?? ['Field', 'What to enter']) as $header)
                                                <th @class(['is-right' => ($loop->last && count($example['headers'] ?? []) > 2)])>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($example['rows'] as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td @class([
                                                        'is-right tabular-nums' => $loop->last && count($row) > 2,
                                                        'text-gray-500 dark:text-gray-400' => $cell === '—' || $cell === '-',
                                                    ])>
                                                        @if(is_array($cell))
                                                            <span class="font-medium">{{ $cell['label'] ?? '' }}</span>
                                                            @if(! empty($cell['hint']))
                                                                <p class="erp-caption">{{ $cell['hint'] }}</p>
                                                            @endif
                                                        @else
                                                            {{ $cell }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif(! empty($example['bullets']))
                            <ul class="space-y-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                @foreach($example['bullets'] as $bullet)
                                    <li class="flex gap-2">
                                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                                        <span>{{ $bullet }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @isset($slot)
            @if(trim($slot) !== '')
                <div>{{ $slot }}</div>
            @endif
        @endisset

        @if($note)
            <p class="erp-quick-guide__note">{{ $note }}</p>
        @endif
    </div>
</details>
