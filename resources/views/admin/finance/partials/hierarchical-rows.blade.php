@foreach($rows as $row)
    <tr class="{{ !empty($row['is_subtotal']) ? 'font-semibold bg-gray-50/80 dark:bg-gray-800/40' : 'hover:bg-white dark:hover:bg-gray-800/50 transition-colors' }}">
        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" style="padding-left: {{ 1 + (($row['level'] ?? 0) * 1.25) }}rem">{{ $row['name'] ?? '' }}</td>
        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ number_format($row['amount'] ?? 0, 2) }}</td>
    </tr>
@endforeach
