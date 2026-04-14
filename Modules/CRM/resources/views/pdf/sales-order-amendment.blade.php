<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order Amendment #{{ $record->amendment_number }} - {{ $record->salesOrder->so_number }}</title>
    <style>
        @page { margin: 30px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; line-height: 1.4; color: #1a202c; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { height: 40px; }
        .title-box { text-align: right; vertical-align: top; }
        .title-box h1 { font-size: 18px; margin: 0; color: #1a365d; }
        .section-title { background-color: #f8fafc; padding: 5px 10px; font-weight: bold; border-left: 3px solid #f59e0b; margin: 15px 0 10px 0; }
        table.comparison-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.comparison-table th { background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; }
        table.comparison-table td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .delta-plus { color: #16a34a; font-weight: bold; }
        .delta-minus { color: #dc2626; font-weight: bold; }
        .snapshot-box { width: 50%; }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td><img src="{{ public_path('images/logo.png') }}" class="logo"></td>
                <td class="title-box">
                    <h1>SALES ORDER AMENDMENT</h1>
                    <p>Amendment #{{ $record->amendment_number }}</p>
                    <div style="font-size: 9px;">Original SO: {{ $record->salesOrder->so_number }}</div>
                    <div style="font-size: 9px;">Date: {{ $record->amendment_date->format('d M Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">AMENDMENT REASON</div>
    <div style="padding: 10px; background: #fffbeb; border: 1px solid #fef3c7;">
        {{ $record->reason }}
    </div>

    <div class="section-title">PERBANDINGAN PERUBAHAN (BEFORE VS AFTER)</div>
    
    <table class="comparison-table">
        <thead>
            <tr>
                <th style="width: 50%;">BEFORE (ORIGINAL)</th>
                <th style="width: 50%;">AFTER (REVISED)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="snapshot-box">
                    <strong>Service Items:</strong><br>
                    @foreach($record->before_snapshot['items'] ?? [] as $item)
                        - {{ $item['description'] }}: IDR {{ number_format($item['total_price'], 0) }}<br>
                    @endforeach
                    <br>
                    <strong>Manpower:</strong><br>
                    @foreach($record->before_snapshot['manpower_details'] ?? [] as $mp)
                        - {{ $mp['job_position_name'] }}: {{ $mp['quantity'] }} Org<br>
                    @endforeach
                </td>
                <td class="snapshot-box">
                    <strong>Service Items:</strong><br>
                    @foreach($record->after_snapshot['items'] ?? [] as $item)
                        - {{ $item['description'] }}: IDR {{ number_format($item['total_price'], 0) }}<br>
                    @endforeach
                    <br>
                    <strong>Manpower:</strong><br>
                    @foreach($record->after_snapshot['manpower_details'] ?? [] as $mp)
                        - {{ $mp['job_position_name'] }}: {{ $mp['quantity'] }} Org<br>
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">KESIMPULAN PERUBAHAN</div>
    @php
        $beforeAmount = collect($record->before_snapshot['items'] ?? [])->sum('total_price');
        $afterAmount = collect($record->after_snapshot['items'] ?? [])->sum('total_price');
        $deltaAmount = $afterAmount - $beforeAmount;
        
        $beforeMP = collect($record->before_snapshot['manpower_details'] ?? [])->sum('quantity');
        $afterMP = collect($record->after_snapshot['manpower_details'] ?? [])->sum('quantity');
        $deltaMP = $afterMP - $beforeMP;
    @endphp
    
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td>Nilai Kontrak (Month)</td>
            <td>: {{ number_format($beforeAmount) }} &rarr; {{ number_format($afterAmount) }}</td>
            <td>Delta: <span class="{{ $deltaAmount >= 0 ? 'delta-plus' : 'delta-minus' }}">{{ $deltaAmount >= 0 ? '+' : '' }}{{ number_format($deltaAmount) }}</span></td>
        </tr>
        <tr>
            <td>Total Manpower</td>
            <td>: {{ $beforeMP }} &rarr; {{ $afterMP }}</td>
            <td>Delta: <span class="{{ $deltaMP >= 0 ? 'delta-plus' : 'delta-minus' }}">{{ $deltaMP >= 0 ? '+' : '' }}{{ $deltaMP }} Org</span></td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="text-align: center; width: 50%;">
                Customer Agreement,<br><br><br><br>
                ( .................................... )
            </td>
            <td style="text-align: center; width: 50%;">
                GDPS Agreement,<br><br><br><br>
                ( .................................... )
            </td>
        </tr>
    </table>
</body>

</html>
