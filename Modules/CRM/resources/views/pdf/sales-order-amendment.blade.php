<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order Amendment #{{ $record->amendment_number }} - {{ $record->salesOrder->so_number }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; color: #000; font-size: 9px; line-height: 1.2; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table { border-bottom: 2px solid #000; margin-bottom: 10px; }
        .logo-box { width: 40%; }
        .logo { height: 40px; }
        .title-box { width: 60%; text-align: right; vertical-align: middle; }
        .title-box h1 { font-size: 16px; margin: 0; font-weight: bold; }
        .title-box p { font-size: 11px; margin: 2px 0; font-weight: bold; color: #f59e0b; }

        .section-header { background-color: #000; color: #fff; padding: 4px 8px; font-weight: bold; font-size: 10px; margin-top: 10px; margin-bottom: 5px; text-transform: uppercase; }
        
        .meta-table td { padding: 3px 5px; border: 1px solid #ccc; }
        .bg-gray { background-color: #f3f4f6; font-weight: bold; width: 18%; }
        .bg-white { background-color: #fff; width: 32%; }

        table.data-table { border: 1px solid #000; }
        table.data-table th { background-color: #e5e7eb; border: 1px solid #000; padding: 5px; font-weight: bold; text-align: center; }
        table.data-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }

        .item-row { padding: 2px 0; border-bottom: 1px dashed #ccc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .delta-plus { color: #16a34a; font-weight: bold; }
        .delta-minus { color: #dc2626; font-weight: bold; }

        .signature-table td { width: 33%; text-align: center; vertical-align: top; border: 1px solid #000; padding: 10px; }
        .sig-space { height: 50px; }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo-box">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
                <div style="font-weight: bold; margin-top: 2px;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
            </td>
            <td class="title-box">
                <h1>SALES ORDER AMENDMENT (SOA)</h1>
                <p>No: {{ $record->amendment_number }}</p>
                <div style="font-weight: normal;">Date: {{ $record->amendment_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Amendment Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Original SO No.</td>
            <td class="bg-white">{{ $record->salesOrder->so_number }}</td>
            <td class="bg-gray">Project Code</td>
            <td class="bg-white">{{ $record->salesOrder->project->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Customer</td>
            <td class="bg-white">{{ $record->salesOrder->customer->name ?? '-' }}</td>
            <td class="bg-gray">Amendment Status</td>
            <td class="bg-white font-bold" style="text-transform: uppercase;">{{ $record->status }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Reason for Change</td>
            <td colspan="3" class="bg-white">{{ $record->reason }}</td>
        </tr>
    </table>

    <div class="section-header">I. Detailed Amendment Comparison (Quantity & Price Changes)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="30%">Service Description / Personnel Rank</th>
                <th width="7%">Unit</th>
                <th width="10%">Qty (Old)</th>
                <th width="10%">Change</th>
                <th width="10%">Qty (New)</th>
                <th width="15%">New Price (IDR)</th>
                <th width="15%">Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f3f4f6;">
                <td colspan="8" class="font-bold">A. Service Items & Costs</td>
            </tr>
            @php 
                $beforeItems = collect($record->before_snapshot['items'] ?? []);
                $afterItems = collect($record->after_snapshot['items'] ?? []);
                // Group by description to find matches
                $allItemNames = $beforeItems->pluck('description')->merge($afterItems->pluck('description'))->unique();
            @endphp

            @foreach($allItemNames as $index => $name)
                @php
                    $old = $beforeItems->firstWhere('description', $name);
                    $new = $afterItems->firstWhere('description', $name);
                    $oldQty = $old['quantity'] ?? 0;
                    $newQty = $new['quantity'] ?? 0;
                    $change = $newQty - $oldQty;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ $new['uom'] ?? ($old['uom'] ?? 'Unit') }}</td>
                    <td class="text-right">{{ number_format($oldQty) }}</td>
                    <td class="text-right {{ $change > 0 ? 'delta-plus' : ($change < 0 ? 'delta-minus' : '') }}">
                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                    </td>
                    <td class="text-right">{{ number_format($newQty) }}</td>
                    <td class="text-right">{{ number_format($new['total_price'] ?? 0) }}</td>
                    <td class="italic" style="font-size: 8px;">{{ $change != 0 ? 'Revised Qty' : '-' }}</td>
                </tr>
            @endforeach

            <tr style="background-color: #f3f4f6;">
                <td colspan="8" class="font-bold">B. Personnel Composition</td>
            </tr>
            @php 
                $beforeMP = collect($record->before_snapshot['manpower_details'] ?? []);
                $afterMP = collect($record->after_snapshot['manpower_details'] ?? []);
                $allMPNames = $beforeMP->pluck('job_position_name')->merge($afterMP->pluck('job_position_name'))->unique();
            @endphp
            @foreach($allMPNames as $index => $pos)
                @php
                    $old = $beforeMP->firstWhere('job_position_name', $pos);
                    $new = $afterMP->firstWhere('job_position_name', $pos);
                    $oldQty = $old['quantity'] ?? 0;
                    $newQty = $new['quantity'] ?? 0;
                    $change = $newQty - $oldQty;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $pos }}</td>
                    <td class="text-center">Person</td>
                    <td class="text-right">{{ number_format($oldQty) }}</td>
                    <td class="text-right {{ $change > 0 ? 'delta-plus' : ($change < 0 ? 'delta-minus' : '') }}">
                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                    </td>
                    <td class="text-right">{{ number_format($newQty) }}</td>
                    <td class="text-right">-</td>
                    <td class="italic" style="font-size: 8px;">{{ $change != 0 ? 'Revised Headcount' : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-header">II. Amendment Summary</div>
    @php
        $sumBefore = collect($beforeItems)->sum('total_price');
        $sumAfter = collect($afterItems)->sum('total_price');
        $deltaAmount = $sumAfter - $sumBefore;
        
        $qtyBefore = collect($beforeMP)->sum('quantity');
        $qtyAfter = collect($afterMP)->sum('quantity');
        $deltaQty = $qtyAfter - $qtyBefore;
    @endphp
    <table class="meta-table">
        <tr class="bg-gray">
            <td width="40%">COMPONENT</td>
            <td width="20%">BEFORE</td>
            <td width="20%">AFTER</td>
            <td width="20%">DELTA</td>
        </tr>
        <tr>
            <td>Total Monthly Amount</td>
            <td class="text-right">{{ number_format($sumBefore) }}</td>
            <td class="text-right">{{ number_format($sumAfter) }}</td>
            <td class="text-right {{ $deltaAmount >= 0 ? 'delta-plus' : 'delta-minus' }}">{{ $deltaAmount >= 0 ? '+' : '' }}{{ number_format($deltaAmount) }}</td>
        </tr>
        <tr>
            <td>Total Personnel</td>
            <td class="text-right">{{ number_format($qtyBefore) }} Person(s)</td>
            <td class="text-right">{{ number_format($qtyAfter) }} Person(s)</td>
            <td class="text-right {{ $deltaQty >= 0 ? 'delta-plus' : 'delta-minus' }}">{{ $deltaQty >= 0 ? '+' : '' }}{{ $deltaQty }} Person(s)</td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <p class="font-bold">Prepared By (CRM)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( ........................................ )</p>
                <p>Account Manager / PIC Sales</p>
            </td>
            <td>
                <p class="font-bold">Verified By (Legal/Finance)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( ........................................ )</p>
                <p>Authorized Representative</p>
            </td>
            <td>
                <p class="font-bold">Acknowledge By (Customer)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( ........................................ )</p>
                <p>Client Representative</p>
            </td>
        </tr>
    </table>
</body>

</html>
