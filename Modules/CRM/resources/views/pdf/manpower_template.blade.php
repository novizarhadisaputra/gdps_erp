<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Manpower Template - {{ $record->name }}</title>
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
        }

        .container {
            width: 100%;
            background: #ffffff;
        }

        .header {
            padding: 40px 50px 24px 50px;
            border-bottom: 2px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .logo {
            height: 44px;
        }

        .company-name {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .doc-type {
            float: right;
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: right;
        }

        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
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
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 28px;
        }

        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
        }

        .description-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 28px;
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.items thead th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
        }

        table.items thead th.right {
            text-align: right;
        }

        table.items thead th.center {
            text-align: center;
        }

        table.items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.items tbody td {
            padding: 9px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            color: #374151;
            vertical-align: top;
        }

        table.items tbody td.right {
            text-align: right;
        }

        table.items tbody td.center {
            text-align: center;
        }

        table.items tfoot td {
            padding: 12px 8px;
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
        }

        table.items tfoot td.right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 9px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #475569;
        }

        .footer {
            padding: 20px 50px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
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
            <div class="doc-type" style="width: 50%; float: right;">
                <div style="font-size: 9px; color: #94a3b8; margin-bottom: 4px;">DOKUMEN INTERNAL</div>
                <div class="doc-title">Manpower Template</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">{{ $record->name }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <div class="section-title">I. Informasi Template</div>
            <table class="info-table">
                <tr>
                    <td width="50%" style="padding-bottom: 12px; vertical-align: top;">
                        <span class="info-label">Nama Template</span>
                        <span class="info-value">{{ $record->name }}</span>
                    </td>
                    <td width="50%" style="padding-bottom: 12px; vertical-align: top;">
                        <span class="info-label">Area Proyek</span>
                        <span class="info-value">{{ $record->projectArea?->name ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td width="33%" style="padding-bottom: 12px; vertical-align: top;">
                        <span class="info-label">Dibuat pada</span>
                        <span class="info-value">{{ $record->created_at->format('d M Y') }}</span>
                    </td>
                    <td width="33%" style="padding-bottom: 12px; vertical-align: top;">
                        <span class="info-label">Total Posisi</span>
                        <span class="info-value">{{ collect($costSimulation['rows'])->count() }} item</span>
                    </td>
                    <td width="34%" style="padding-bottom: 12px; vertical-align: top;">
                        <span class="info-label">Total Manpower</span>
                        <span class="info-value">{{ collect($costSimulation['rows'])->sum('qty') }} orang</span>
                    </td>
                </tr>
            </table>

            @if ($record->description)
                <div class="section-title">II. Deskripsi</div>
                <div class="description-box">{{ $record->description }}</div>
            @endif

            <div class="section-title">{{ $record->description ? 'III.' : 'II.' }} Rincian Simulasi Biaya Manpower</div>

            <table class="items">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="28%">Posisi Pekerjaan</th>
                        <th class="center" width="5%">Qty</th>
                        <th class="right" width="13%">Gaji Pokok</th>
                        <th class="right" width="12%">Tunjangan</th>
                        <th class="right" width="12%">BPJS & Pajak</th>
                        <th class="right" width="12%">THR/Komp</th>
                        <th class="right" width="14%">Total Subcost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($costSimulation['rows'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['job_position_name'] }}</td>
                            <td class="center">{{ $row['qty'] }}</td>
                            <td class="right">Rp {{ number_format($row['basic_salary'], 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($row['total_allowances'], 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($row['bpjs_total'], 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($row['thr_compensation'], 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($row['line_total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right"
                            style="font-size: 10px; letter-spacing: 0.5px; text-transform: uppercase;">Total Estimasi
                            Biaya / Bulan</td>
                        <td class="right" style="font-size: 13px;">Rp
                            {{ number_format($costSimulation['total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            Dicetak pada: {{ now()->format('d M Y H:i') }} WIB &mdash; Dokumen ini digenerate secara otomatis oleh
            sistem ERP PT. GDPS. Evaluasi tagihan BPJS berdasarkan parameter {{ $record->employee_type }} dan asuransi
            kecelakaan kerja {{ $record->risk_level }}%
            ({{ $record->is_labor_intensive ? 'Padat Karya' : 'Normal' }}).
        </div>
    </div>
</body>

</html>
