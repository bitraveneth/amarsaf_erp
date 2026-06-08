<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 11px;
            margin: 24px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .meta {
            color: #6b7280;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f9fafb;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tr.row-alt td {
            background: #fcfcfd;
        }

        .note {
            margin-top: 14px;
            color: #92400e;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">Generated {{ $generatedAt->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $rowIndex => $row)
                <tr @class(['row-alt' => $rowIndex % 2 === 1])>
                    @foreach((array) $row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">No records to export.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($truncated ?? false)
        <p class="note">
            PDF export is limited to the first 500 rows. Download CSV for the full {{ number_format($totalRows) }} records.
        </p>
    @endif
</body>
</html>
