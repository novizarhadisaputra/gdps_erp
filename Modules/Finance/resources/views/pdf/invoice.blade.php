<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - {{ $record->invoice_number }}</title>
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
            font-size: 11px;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            background: #ffffff;
        }

        .header {
            padding: 40px 50px 20px 50px;
            border-bottom: 2px solid #f1f5f9;
            background-color: #fcfcfc;
        }

        .logo {
            height: 40px;
            margin-bottom: 10px;
        }

        .document-type {
            float: right;
            text-align: right;
        }

        .document-type h1 {
            font-size: 24px;
            margin: 0;
            color: #0f172a;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        .document-type p {
            font-size: 11px;
            color: #2563eb;
            margin: 2px 0 0 0;
            font-weight: bold;
        }

        .content {
            padding: 30px 50px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            padding: 10px 15px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            width: 30%;
        }

        td {
            padding: 10px 15px;
            border: 1px solid #f1f5f9;
            vertical-align: top;
            color: #334155;
            font-size: 11px;
        }

        .info-value {
            font-weight: bold;
            color: #0f172a;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 20px 50px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }

        .amount-highlight {
            font-size: 14px;
            color: #1e293b;
        }

        .total-section {
            background-color: #f8fafc;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="float: left; width: 50%;">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
                <div style="font-size: 9px; font-weight: bold; color: #64748b;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
            </div>
            <div class="document-type" style="float: right; width: 50%;">
                <h1>INVOICE</h1>
                <p>{{ $record->invoice_number }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ $record->invoice_date->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <div class="section-title">Invoiced To</div>
            <table>
                <tr>
                    <th>Customer</th>
                    <td class="info-value">{{ $record->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $record->customer->address ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">Reference</div>
            <table>
                <tr>
                    <th>Sales Order</th>
                    <td>{{ $record->salesOrder->so_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th>WCR Number</th>
                    <td>{{ $record->workCompletionReport->report_number ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">Payment Terms</div>
            <table>
                <tr>
                    <th>Due Date</th>
                    <td class="info-value" style="color: #dc2626;">{{ $record->due_date->format('d M Y') }}</td>
                </tr>
            </table>

            <div class="section-title">Amount Breakdown</div>
            <table>
                <tr>
                    <th>Subtotal</th>
                    <td>IDR {{ number_format($record->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Tax Amount</th>
                    <td>IDR {{ number_format($record->tax_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-section">
                    <th>Total Amount</th>
                    <td class="amount-highlight">IDR {{ number_format($record->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>

            <div class="section-title">Status</div>
            <table>
                <tr>
                    <th>Payment Status</th>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $record->status->getLabel() }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Financial Document System
        </div>
    </div>
</body>

</html>
