<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Lembar Verifikasi Dokumen</title>
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
            font-size: 12px;
        }

        .container {
            width: 100%;
            background: #ffffff;
        }

        .header {
            padding: 50px 50px 30px 50px;
            border-bottom: 1px solid #f1f5f9;
            background-color: #fafafa;
        }

        .logo-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo {
            height: 48px;
        }

        .company-name {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 8px;
        }

        .status-badge {
            float: right;
            padding: 6px 16px;
            background-color: #ecfdf5;
            color: #047857;
            border-radius: 99px;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #d1fae5;
        }

        .checksum {
            float: right;
            clear: both;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 5px;
            font-family: monospace;
        }

        .title-section {
            text-align: center;
            margin-top: 40px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #64748b;
            font-size: 13px;
        }

        .content {
            padding: 40px 50px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 40px;
        }

        .info-item {
            padding: 12px 0;
            vertical-align: top;
        }

        .info-label {
            font-size: 10px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            display: block;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        .statement-box {
            background-color: #f5f3ff;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #ede9fe;
            margin-bottom: 40px;
            line-height: 1.6;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 18px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .signer-name {
            font-weight: bold;
            color: #1e293b;
            font-size: 13px;
        }

        .signer-role {
            font-size: 11px;
            color: #1e293b;
            font-weight: bold;
        }

        .signer-unit {
            font-size: 10px;
            color: #64748b;
        }

        .signed-date {
            font-weight: bold;
            font-size: 13px;
        }

        .signed-time {
            font-size: 10px;
            color: #94a3b8;
        }

        .scanned-tag {
            font-size: 9px;
            background-color: #eff6ff;
            color: #2563eb;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #dbeafe;
            text-transform: uppercase;
            font-weight: bold;
        }

        .footer {
            padding: 40px 50px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="width: 50%; float: left;">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
                <div class="company-name">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
            </div>
            <div style="width: 50%; float: right; text-align: right;">
                <div class="status-badge">VALID & ASLI</div>
                <div class="checksum">Checksum: {{ substr(md5($document->id . $signed_at), 0, 12) }}</div>
            </div>
            <div style="clear: both;"></div>

            <div class="title-section">
                <div class="title">Lembar Verifikasi</div>
                <div class="subtitle">Informasi Keaslian Tanda Tangan Elektronik</div>
            </div>
        </div>

        <div class="content">
            <div class="section-title">I. Informasi Dokumen</div>
            <table class="info-grid">
                <tr>
                    <td class="info-item" width="50%">
                        <span class="info-label">Nomor Registrasi</span>
                        <span class="info-value">{{ $document->document_number ?? $document->id }}</span>
                    </td>
                    <td class="info-item" width="50%">
                        <span class="info-label">Tipe Dokumen</span>
                        <span class="info-value">{{ class_basename($document) }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="info-item">
                        <span class="info-label">Pelanggan</span>
                        <span class="info-value">{{ $document->customer->name ?? '-' }}</span>
                    </td>
                    <td class="info-item">
                        <span class="info-label">Status Akhir</span>
                        <span class="info-value">{{ ucfirst($document->status->value ?? $document->status) }}</span>
                    </td>
                </tr>
            </table>

            <div class="statement-box">
                Dinyatakan <strong>VALID</strong> dan <strong>Telah Disetujui</strong> oleh PT. GARUDA DAYA PRATAMA
                SEJAHTERA dan dengan ketentuan yang berlaku. Dokumen ini telah ditandatangani secara elektronik sehingga
                tidak memerlukan tanda tangan basah.
            </div>

            <div class="section-title">II. Riwayat Persetujuan</div>
            <table>
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Jabatan / Unit</th>
                        <th>Penandatangan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($signatures as $index => $sig)
                        <tr>
                            <td valign="top">{{ $index + 1 }}</td>
                            <td valign="top">
                                <div class="signer-role">{{ $sig->role }}</div>
                                <div class="signer-unit">{{ $sig->user->unit->name ?? 'Internal' }}</div>
                            </td>
                            <td valign="top">
                                <div class="signer-name">{{ $sig->user->name ?? 'Unknown' }}</div>
                                @if ($signer->id === $sig->user_id)
                                    <span class="scanned-tag">Dipindai</span>
                                @endif
                            </td>
                            <td valign="top">
                                <div class="signed-date">{{ $sig->signed_at->format('d M Y') }}</div>
                                <div class="signed-time">{{ $sig->signed_at->format('H:i:s') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            Dicetak pada: {{ now()->format('d M Y H:i:s') }} WIB. Digunakan untuk keperluan verifikasi internal dan
            eksternal PT. GDPS.
        </div>
    </div>
</body>

</html>
