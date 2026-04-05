@php
    $pa = $record->profitabilityAnalysis ?? $record->lead?->latestProfitabilityAnalysis;
    $gi = $record->lead?->latestGeneralInformation;
    $customerName = $record->customer->name ?? '........';
    $productClusterName = $pa->productCluster->name ?? ($gi->productCluster->name ?? ($record->lead->productCluster->name ?? 'Integrated Facility Management'));
    $location = $pa->projectArea->name ?? ($gi->location ?? '-');
    $revenue = $pa ? $pa->revenue_per_month : $record->amount;
    $paymentTerm = $pa->paymentTerm->name ?? ($record->lead->paymentTerm->name ?? '60');
    $ams = $record->lead->ams ?? ($record->lead->user ?? null);
    $amsName = $ams->name ?? 'Account Manager';
    $amsEmail = $ams->email ?? '';
    $amsPhone = $ams->phone ?? ($ams->contacts[0]['phone'] ?? ''); // Employee object might have phone, User might not.
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Proposal - {{ $record->proposal_number }}</title>
    <style>
        @page {
            margin: 0.5in 0.75in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.6;
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .logo {
            height: 45px;
            margin-bottom: 10px;
        }

        .header-content {
            clear: both;
            margin-top: 20px;
        }

        .date-location {
            text-align: right;
            margin-bottom: 15px;
        }

        .recipient {
            margin-bottom: 30px;
        }

        .document-title {
            text-align: center;
            margin: 30px 0;
            text-decoration: underline;
        }

        .document-title h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .no-border table, .no-border tr, .no-border td {
            border: none;
            padding: 2px 0;
        }

        .pricing-table th {
            background-color: #0f172a;
            color: white;
            text-align: center;
        }

        .pricing-table-total {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .pricing-table .amount {
            text-align: right;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
        }

        .signature-box {
            width: 50%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }

        .signature-title {
            margin-bottom: 60px;
        }

        .footer {
            position: fixed;
            bottom: 0.2in;
            left: 0.75in;
            right: 0.75in;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 5px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" class="logo">
        <div style="font-size: 10px; font-weight: bold; color: #64748b;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
    </div>

    <div class="date-location">
        Tangerang, {{ $record->submission_date ? $record->submission_date->format('d F Y') : now()->format('d F Y') }}
    </div>

    <div class="recipient">
        Yth. Bapak/Ibu <strong>........</strong><br>
        PT <strong>{{ $customerName }}</strong><br>
        {{ $record->customer->address ?? 'Di tempat' }}
    </div>

    <div style="margin-bottom: 25px;">
        <table class="no-border" style="width: 100%;">
            <tr>
                <td style="width: 150px;">Nomor kami / Our number</td>
                <td>: {{ $record->proposal_number }}</td>
            </tr>
            <tr>
                <td>Perihal / Subject</td>
                <td>: <strong>Proposal Penawaran Harga Layanan {{ $productClusterName }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <p>Dengan hormat,</p>
        <p>Kami dari PT Garuda Daya Pratama Sejahtera (PT GDPS) mengucapkan terima kasih atas kesempatan yang diberikan sehingga dapat menyampaikan Proposal Penawaran Harga Layanan <strong>{{ $productClusterName }}</strong> dengan nomor <strong>{{ $record->proposal_number }}</strong>. Penawaran ini merupakan tindak lanjut atas kebutuhan perusahaan Bapak/Ibu yang telah diskusikan sebelumnya.</p>
    </div>

    <div class="section">
        <div class="section-title">1. Harga & Detail Layanan</div>
        <table class="pricing-table">
            <thead>
                <tr>
                    <th>Service Description</th>
                    <th>Site / Location</th>
                    <th>Price (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $record->title }}</td>
                    <td>{{ $location }}</td>
                    <td class="amount">{{ number_format($revenue, 0, ',', '.') }}</td>
                </tr>
                <tr class="pricing-table-total">
                    <td colspan="2" style="text-align: right;">Total (Excl. PPN 11%)</td>
                    <td class="amount">{{ number_format($revenue, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($pa)
        <p style="margin-top: 10px;">
            <strong>Ketentuan harga paket di atas mencakup:</strong>
        </p>
        <ul style="padding-left: 20px;">
            @php
                $manpower = $pa->manpower_requirements ?? [];
                $totalQuantity = collect($manpower)->sum('quantity');
                $isLaborIntensive = collect($manpower)->contains('is_labor_intensive', true);
            @endphp
            @if($totalQuantity > 0)
                <li>Personil berjumlah {{ $totalQuantity }} orang.</li>
            @endif
            <li>Personil yang ditugaskan telah memenuhi UU Ketenagakerjaan dan peraturan terkait lainnya yang berlaku.</li>
            <li>Remunerasi gaji pokok memakai UMK/UMP {{ now()->year }} setempat.</li>
            @if($pa->management_fee_rate > 0)
                <li>Management fee {{ $pa->management_fee_rate }}%.</li>
            @endif
        </ul>
        @endif
    </div>

    @if($gi)
    <div class="section">
        <div class="section-title">2. Ketentuan & Scope of Work</div>
        <div style="white-space: pre-line;">
            {{ $gi->scope_of_work }}
        </div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">3. Term of Payment (TOP)</div>
        <p>Pembayaran dilakukan dalam waktu <strong>{{ $paymentTerm }} hari</strong> setelah invoice diterima secara lengkap dan benar oleh pihak PT {{ $customerName }}.</p>
    </div>

    <div class="section">
        <div class="section-title">4. Masa Berlaku Proposal</div>
        <p>Proposal penawaran ini berlaku selama <strong>30 (tiga puluh) hari</strong> kalender terhitung sejak tanggal dokumen ini diterbitkan.</p>
    </div>

    <div class="section">
        <p>Demikian proposal penawaran ini kami sampaikan. Besar harapan kami kiranya kemitraan ini dapat terjalin dengan baik dan memberikan nilai tambah bagi operasional perusahaan Bapak/Ibu.</p>
        <p>Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box" style="float: left;">
            <div class="signature-title">Diajukan oleh,<br><strong>PT Garuda Daya Pratama Sejahtera</strong></div>
            <div style="margin-top: 40px;">
                <strong>{{ $amsName }}</strong><br>
                <span>Account Manager & Sales</span><br>
                @if($amsEmail)<span style="font-size: 9px; color: #64748b;">{{ $amsEmail }}</span>@endif
            </div>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-title">Disetujui oleh,<br><strong>PT {{ $customerName }}</strong></div>
            <div style="margin-top: 40px;">
                <strong>(....................................)</strong><br>
                <span>Jabatan: ......................</span>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    @if($pa && !empty($pa->manpower_requirements))
    <div class="page-break"></div>
    <div class="section">
        <div class="section-header" style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 14px; text-decoration: underline;">LAMPIRAN I: REMUNERASI MANPOWER</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jabatan / Position</th>
                    <th>Quantity</th>
                    <th>Gaji Pokok / Basic Salary</th>
                    <th>Total Monthly</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pa->manpower_requirements as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item['job_position_name'] ?? '-' }}</td>
                    <td style="text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="amount">{{ number_format($item['unit_cost'] ?? 0, 0, ',', '.') }}</td>
                    <td class="amount">{{ number_format($item['total_monthly_cost'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Soewarna Business Park Block D Lot 1-2, Bandara Soekarno-Hatta &nbsp;&bull;&nbsp; www.garudapratama.com
    </div>
</body>

</html>
