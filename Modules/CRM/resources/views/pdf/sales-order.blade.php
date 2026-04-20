<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order - {{ $record->so_number }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 9px;
            line-height: 1.2;
        }

        .container {
            width: 100%;
            border: 1px solid #000;
            padding: 10px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }

        .logo-box { width: 40%; }
        .logo { height: 40px; }

        .title-box {
            width: 60%;
            text-align: right;
            vertical-align: middle;
        }

        .title-box h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        .title-box p { font-size: 11px; margin: 2px 0; font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .meta-table td {
            padding: 3px 5px;
            border: 1px solid #ccc;
        }

        .bg-gray { background-color: #f3f4f6; font-weight: bold; width: 18%; }
        .bg-white { background-color: #fff; width: 32%; }

        .section-header {
            background-color: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        table.data-table th {
            background-color: #e5e7eb;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .total-box {
            float: right;
            width: 45%;
        }
        .total-box td {
            padding: 4px 6px;
            border: 1px solid #000;
        }

        .signature-table {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 10px;
        }
        .sig-space { height: 50px; }

        .terms-table td {
            border: 1px solid #ccc;
            padding: 3px 5px;
        }
    </style>
</head>

<<body>
    <table class="header-table">
        <tr>
            <td class="logo-box">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
                <div style="font-weight: bold; margin-top: 2px;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
            </td>
            <td class="title-box">
                <h1>SALES ORDER / SURAT PESANAN</h1>
                <p>{{ $record->so_number }}</p>
                <div style="font-weight: normal;">Date: {{ $record->order_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Project Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Project Name</td>
            <td class="bg-white">{{ $record->project->name ?? '-' }}</td>
            <td class="bg-gray">Project Code</td>
            <td class="bg-white">{{ $record->project->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Customer</td>
            <td class="bg-white">{{ $record->customer->name ?? '-' }}</td>
            <td class="bg-gray">Sales PIC</td>
            <td class="bg-white">{{ $record->salesPic->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Service Type</td>
            <td class="bg-white">{{ $record->service_type ?? '-' }}</td>
            <td class="bg-gray">Project Manager</td>
            <td class="bg-white">{{ $record->projectManager->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Location</td>
            <td class="bg-white">{{ $record->job_location ?? '-' }}</td>
            <td class="bg-gray">Manpower Total</td>
            <td class="bg-white">{{ number_format($record->manpower_initial_qty) }} Person(s)</td>
        </tr>
    </table>

    <div class="section-header">I. Service & Personnel Details (Monthly Estimation)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Service Description / Personnel Rank</th>
                <th width="10%">Unit</th>
                <th width="10%">Qty</th>
                <th width="15%">Unit Price (IDR)</th>
                <th width="20%">Total / Month</th>
            </tr>
        </thead>
        <tbody>
            {{-- A. Service Items --}}
            @php $items = $record->content_config['items'] ?? []; @endphp
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['uom'] ?? 'Unit' }}</td>
                    <td class="text-right">{{ number_format($item['quantity'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center italic">No service items found.</td></tr>
            @endforelse

            {{-- Separator for Manpower --}}
            <tr style="background-color: #f3f4f6;">
                <td colspan="6" class="font-bold" style="padding: 5px 10px;">Personnel Composition</td>
            </tr>

            @php $manpower = $record->content_config['manpower_details'] ?? []; @endphp
            @forelse($manpower as $index => $mp)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $mp['job_position_name'] ?? '-' }}</td>
                    <td class="text-center">Person</td>
                    <td class="text-right">{{ number_format($mp['quantity'] ?? 0) }}</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center italic text-gray">No personnel details listed in this section.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        <table>
            <tr>
                <td class="bg-gray" style="width: 50%;">Subtotal</td>
                <td class="text-right font-bold" style="width: 50%;">{{ number_format($record->amount / (1 + ($record->tax_percentage/100) + ($record->management_fee_percentage/100)), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bg-gray">Mgt Fee ({{ $record->management_fee_percentage }}%)</td>
                <td class="text-right">{{ number_format($record->amount * ($record->management_fee_percentage/100), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bg-gray">VAT ({{ $record->tax_percentage }}%)</td>
                <td class="text-right">{{ number_format($record->amount * ($record->tax_percentage/100), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bg-gray" style="background-color: #000; color: #fff;">Grand Total / Month</td>
                <td class="text-right font-bold" style="background-color: #f3f4f6;">IDR {{ number_format($record->amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <div class="section-header">III. Contractual Terms</div>
    <table class="terms-table">
        <tr>
            <td class="bg-gray">Payment Terms</td>
            <td>{{ $record->payment_terms ?? 'As per company policy' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Probation Period</td>
            <td>{{ $record->probation_period ?? '3 Months' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Replacement SLA</td>
            <td>{{ $record->replacement_sla ?? '3 Working Days' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Reporting</td>
            <td>{{ $record->reporting_schedule ?? 'Last Friday of the month' }}</td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <p class="font-bold">Proposed By (Internal)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( {{ $record->salesPic->name ?? 'Account Manager' }} )</p>
                <p>PIC Sales / AMS</p>
            </td>
            <td>
                <p class="font-bold">Verified By (Internal)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( ........................................ )</p>
                <p>Finance / Operation Head</p>
            </td>
            <td>
                <p class="font-bold">Approved By (Customer)</p>
                <div class="sig-space"></div>
                <p class="font-bold">( ........................................ )</p>
                <p>Authorized Representative</p>
            </td>
        </tr>
    </table>
</body>

</html>
