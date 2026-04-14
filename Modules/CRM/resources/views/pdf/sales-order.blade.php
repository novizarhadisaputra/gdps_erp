<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order - {{ $record->so_number }}</title>
    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1a202c;
            font-size: 10px;
            line-height: 1.4;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            height: 45px;
        }

        .title-box {
            text-align: right;
            vertical-align: top;
        }

        .title-box h1 {
            font-size: 20px;
            margin: 0;
            color: #1a365d;
            text-transform: uppercase;
        }

        .title-box p {
            margin: 5px 0 0 0;
            font-weight: bold;
            color: #2563eb;
        }

        .section-title {
            background-color: #f8fafc;
            padding: 5px 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-left: 3px solid #2563eb;
            margin: 15px 0 10px 0;
            font-size: 11px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-grid td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 30%;
            color: #64748b;
        }

        .value {
            width: 70%;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table.data-table th {
            background-color: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            text-transform: uppercase;
        }

        table.data-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-section {
            margin-top: 10px;
            float: right;
            width: 40%;
        }

        .total-row {
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 0;
        }

        .grand-total {
            font-size: 12px;
            font-weight: bold;
            color: #1a365d;
            background-color: #eff6ff;
            padding: 8px;
            margin-top: 5px;
        }

        .terms-box {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #e2e8f0;
            background-color: #fdfdfd;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
        }

        .sig-block {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .sig-space {
            height: 80px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <img src="{{ public_path('images/logo.png') }}" class="logo">
                    <div style="font-size: 8px; color: #64748b; margin-top: 5px;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
                </td>
                <td class="title-box">
                    <h1>SALES ORDER</h1>
                    <p>{{ $record->so_number }}</p>
                    <div style="font-size: 9px; color: #64748b;">Date: {{ $record->order_date->format('d M Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-grid">
        <tr>
            <td class="label">Customer Name</td>
            <td class="value">: {{ $record->customer->name ?? '-' }}</td>
            <td class="label">Project Code</td>
            <td class="value">: {{ $record->project->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Service Type</td>
            <td class="value">: {{ $record->service_type ?? '-' }}</td>
            <td class="label">Sales PIC</td>
            <td class="value">: {{ $record->salesPic->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Work Scheme</td>
            <td class="value">: {{ $record->productCluster?->name ?? '-' }}</td>
            <td class="label">Project Manager</td>
            <td class="value">: {{ $record->projectManager->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td class="value">: {{ $record->job_location ?? '-' }}</td>
            <td class="label">Manpower Qty</td>
            <td class="value">: {{ $record->manpower_initial_qty ?? 0 }} Person(s)</td>
        </tr>
    </table>

    <div class="section-title">I. HARGA & DETAIL LAYANAN (ESTIMASI)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Item Description</th>
                <th>UoM</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total / Month</th>
            </tr>
        </thead>
        <tbody>
            @php $items = $record->content_config['items'] ?? []; @endphp
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['uom'] ?? 'Unit' }}</td>
                    <td class="text-right">{{ number_format($item['quantity'] ?? 0) }}</td>
                    <td class="text-right">IDR {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">IDR {{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center italic">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 100%;">
            <tr class="total-row">
                <td style="color: #64748b;">Subtotal</td>
                <td class="text-right">IDR {{ number_format($record->amount / (1 + ($record->tax_percentage/100) + ($record->management_fee_percentage/100)), 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td style="color: #64748b;">Management Fee ({{ $record->management_fee_percentage }}%)</td>
                <td class="text-right">IDR {{ number_format($record->amount * ($record->management_fee_percentage/100), 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td style="color: #64748b;">VAT ({{ $record->tax_percentage }}%)</td>
                <td class="text-right">IDR {{ number_format($record->amount * ($record->tax_percentage/100), 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>GRAND TOTAL / MONTH</td>
                <td class="text-right">IDR {{ number_format($record->amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <div class="section-title">II. KOMPOSISI PERSONIL</div>
    <table class="data-table" style="width: 50%;">
        <thead>
            <tr>
                <th>No</th>
                <th>Jabatan / Posisi</th>
                <th class="text-right">Qty</th>
            </tr>
        </thead>
        <tbody>
            @php $manpower = $record->content_config['manpower_details'] ?? []; @endphp
            @forelse($manpower as $index => $mp)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $mp['job_position_name'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($mp['quantity'] ?? 0) }} Org</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center italic">No manpower details found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">III. KETENTUAN UMUM</div>
    <div class="terms-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 30%;">Term of Payment</td>
                <td>: {{ $record->payment_terms ?? 'Atau sesuai kebijakan perusahaan.' }}</td>
            </tr>
            <tr>
                <td>Probation Period</td>
                <td>: {{ $record->probation_period ?? '3 Months' }}</td>
            </tr>
            <tr>
                <td>Replacement SLA</td>
                <td>: {{ $record->replacement_sla ?? '3 Working Days' }}</td>
            </tr>
            <tr>
                <td>Monthly Report</td>
                <td>: {{ $record->reporting_schedule ?? 'Last Friday of the month' }}</td>
            </tr>
        </table>
    </div>

    <table class="signature-section">
        <tr>
            <td class="sig-block">
                <p>Disetujui Oleh Customer,</p>
                <div class="sig-space"></div>
                <p class="sig-name">( ........................................ )</p>
                <p>Nama & Jabatan</p>
            </td>
            <td class="sig-block">
                <p>PT Garuda Daya Pratama Sejahtera,</p>
                <div class="sig-space"></div>
                <p class="sig-name">{{ $record->salesPic->name ?? 'Account Manager' }}</p>
                <p>Account Manager / PIC Sales</p>
            </td>
        </tr>
    </table>

</body>

</html>
