<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Manpower Costing Template - {{ $record->name }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 10px;
        }

        .container {
            width: 100%;
            background: #ffffff;
        }

        .header {
            padding: 30px 40px 20px 40px;
            border-bottom: 2px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .logo {
            height: 36px;
        }

        .company-name {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }

        .content {
            padding: 20px 40px;
        }

        table.costing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.costing-table th {
            background-color: #1e293b;
            color: #ffffff;
            padding: 8px;
            text-align: center;
            border: 1px solid #334155;
            text-transform: uppercase;
            font-size: 9px;
        }

        table.costing-table td {
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
        }

        .label-cell {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
            width: 30%;
        }

        .data-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            width: 23%;
        }

        .subtotal-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        .total-row {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
        }

        .total-row td {
            border-color: #1e293b;
        }

        .footer {
            padding: 15px 40px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="width: 50%; float: left;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
                <div class="company-name">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
            </div>
            <div style="width: 50%; float: right; text-align: right;">
                <div style="font-size: 8px; color: #94a3b8;">DOKUMEN PENAWARAN / COSTING</div>
                <div class="doc-title">Costing Manpower R1</div>
                <div style="font-size: 9px; color: #64748b;">Template: {{ $record->name }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <table class="costing-table">
                <thead>
                    <tr>
                        <th class="label-cell">Komponen Biaya (Field)</th>
                        @php
                            $displayItems = collect($costSimulation['rows'])->take(3); // Show up to 3 examples as D, E, F
                        @endphp
                        @foreach($displayItems as $index => $item)
                            <th>Contoh {{ chr(68 + $index) }}<br><small>{{ $item['job_position_name'] }}</small></th>
                        @endforeach
                        @if($displayItems->count() < 3)
                            @for($i = $displayItems->count(); $i < 3; $i++)
                                <th>Placeholder {{ chr(68 + $i) }}</th>
                            @endfor
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstBreakdown = $displayItems->first()['breakdown'] ?? [];
                        $labels = array_keys($firstBreakdown);
                    @endphp

                    @foreach($labels as $label)
                        <tr class="{{ str_contains($label, 'SUBTOTAL') ? 'subtotal-row' : (str_contains($label, 'TOTAL') ? 'total-row' : '') }}">
                            <td class="label-cell">{{ $label }}</td>
                            @foreach($displayItems as $item)
                                <td class="data-cell">
                                    Rp {{ number_format($item['breakdown'][$label] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            @if($displayItems->count() < 3)
                                @for($i = $displayItems->count(); $i < 3; $i++)
                                    <td class="data-cell">-</td>
                                @endfor
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            Dicetak pada: {{ now()->format('d M Y H:i') }} WIB &mdash; Dokumen ini adalah simulasi biaya manpower berdasarkan parameter UMK Area: {{ $record->projectArea?->name }} ({{ $record->year }}).
        </div>
    </div>
</body>

</html>
