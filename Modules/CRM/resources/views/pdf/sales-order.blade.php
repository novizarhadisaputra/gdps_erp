<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order - {{ $record->so_number }}</title>
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

        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 33%;
            text-align: center;
            padding: 15px;
            vertical-align: top;
        }

        .signature-role {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .signature-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 10px;
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
                <h1>SALES ORDER</h1>
                <p>{{ $record->so_number }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ $record->order_date->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <div class="section-title">General Information</div>
            <table>
                <tr>
                    <th>Customer</th>
                    <td class="info-value">{{ $record->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Project Code</th>
                    <td>{{ $record->project->code ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Service Type</th>
                    <td>{{ $record->service_type ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Job Location</th>
                    <td>{{ $record->job_location ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">Order Details</div>
            <table>
                <tr>
                    <th>Total Amount</th>
                    <td class="info-value amount-highlight">IDR {{ number_format($record->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Management Fee</th>
                    <td>{{ $record->management_fee_percentage ?? 0 }}%</td>
                </tr>
                <tr>
                    <th>Tax</th>
                    <td>{{ $record->tax_percentage ?? 0 }}%</td>
                </tr>
            </table>

            <div class="section-title">Operations & Manpower</div>
            <table>
                <tr>
                    <th>Initial Quantity</th>
                    <td>{{ $record->manpower_initial_qty ?? 0 }} Persons</td>
                </tr>
                <tr>
                    <th>Payment Terms</th>
                    <td>{{ $record->payment_terms ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Reporting Schedule</th>
                    <td>{{ $record->reporting_schedule ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">Management</div>
            <table>
                <tr>
                    <th>Sales PIC</th>
                    <td>{{ $record->salesPic->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Project Manager</th>
                    <td>{{ $record->projectManager->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $record->status->getLabel() }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Digital Document System
        </div>
    </div>
</body>

</html>
