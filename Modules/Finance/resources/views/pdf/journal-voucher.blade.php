<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Voucher - {{ $record->number }}</title>
    <style>
        @page {
            margin: 0.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 180px;
        }

        .voucher-title {
            text-align: right;
            vertical-align: bottom;
        }

        .voucher-title h1 {
            margin: 0;
            color: #1e40af;
            font-size: 20px;
            letter-spacing: 2px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 100px;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .main-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }

        .main-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .total-row {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .footer-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 25%;
            text-align: center;
            border: 1px solid #d1d5db;
            padding: 10px;
            height: 80px;
            vertical-align: top;
        }

        .signature-space {
            height: 60px;
        }

        .description-box {
            border: 1px solid #d1d5db;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #fdfdfd;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <img src="{{ $logo }}" class="logo">
            </td>
            <td class="voucher-title">
                <h1>JOURNAL VOUCHER</h1>
                <div style="font-size: 12px; font-weight: bold;">{{ $record->number }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Date</td>
            <td width="20">:</td>
            <td>{{ $record->date->format('d F Y') }}</td>
            <td class="label">Source</td>
            <td width="20">:</td>
            <td>{{ $sourceType ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Reference No</td>
            <td width="20">:</td>
            <td>{{ $record->reference?->number ?? '-' }}</td>
            <td class="label">Status</td>
            <td width="20">:</td>
            <td style="text-transform: uppercase; font-weight: bold;">{{ $record->status }}</td>
        </tr>
    </table>

    <div class="description-box">
        <div class="label" style="margin-bottom: 5px;">Description:</div>
        <div>{{ $record->description ?: 'No description provided.' }}</div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="15%">Account Code</th>
                <th width="25%">Account Name</th>
                <th width="30%">Memo / Description</th>
                <th width="15%">Debit</th>
                <th width="15%">Credit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach ($record->items as $item)
                @php
                    $totalDebit += $item->debit;
                    $totalCredit += $item->credit;
                @endphp
                <tr>
                    <td class="text-center">{{ $item->chartOfAccount->code ?? '-' }}</td>
                    <td>{{ $item->chartOfAccount->name ?? '-' }}</td>
                    <td>{{ $item->note }}</td>
                    <td class="text-right">{{ number_format($item->debit, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->credit, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalCredit, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <div>Prepared By:</div>
                <div class="signature-space"></div>
                <div class="font-bold">Staff Accounting</div>
            </td>
            <td>
                <div>Checked By:</div>
                <div class="signature-space"></div>
                <div class="font-bold">Manager Finance</div>
            </td>
            <td>
                <div>Approved By:</div>
                <div class="signature-space"></div>
                <div class="font-bold">GM Finance & Tax</div>
            </td>
            <td>
                <div>Receiver (if any):</div>
                <div class="signature-space"></div>
                <div class="font-bold">.........................</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; font-style: italic; color: #666; font-size: 8px;">
        Printed on: {{ now()->format('d/m/Y H:i:s') }} | Voucher ID: {{ $record->id }}
    </div>
</body>

</html>
