<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Manpower Costing Details - {{ $record->name }}</title>
    <style>
        @page {
            margin: 110px 40px 60px 40px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 9px;
            line-height: 1.3;
        }

        /* Repeating Header on each page */
        header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 70px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }

        .header-logo-container {
            width: 55%;
            float: left;
        }

        .logo {
            height: 28px;
        }

        .company-name {
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            letter-spacing: 0.5px;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .header-info-container {
            width: 45%;
            float: right;
            text-align: right;
        }

        .doc-category {
            font-size: 7px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin: 2px 0;
        }

        .doc-subtitle {
            font-size: 8px;
            color: #475569;
        }

        /* Repeating Footer on each page */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5px;
            color: #64748b;
        }

        .footer-left {
            width: 60%;
            float: left;
        }

        .footer-right {
            width: 40%;
            float: right;
            text-align: right;
        }

        /* Typography & Layout elements */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-left: 3px solid #1e293b;
            padding-left: 6px;
        }

        .parameter-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .parameter-grid td {
            padding: 4px 8px;
            vertical-align: top;
            border: 1px solid #f1f5f9;
        }

        .parameter-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
            background-color: #f8fafc;
        }

        .parameter-val {
            color: #1e293b;
            width: 32%;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            padding: 6px 8px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #1e293b;
            text-transform: uppercase;
            font-size: 8.5px;
        }

        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-subtotal {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .bg-total {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        .bg-grand {
            background-color: #0f172a;
            color: #ffffff !important;
            font-weight: bold;
        }

        .bg-grand td {
            border-color: #0f172a !important;
        }

        .badge-borne {
            color: #16a34a;
            font-weight: bold;
        }

        .badge-deduct {
            color: #64748b;
        }

        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .spacer {
            height: 15px;
        }

        /* Detailed position box */
        .position-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 20px;
            background-color: #f8fafc;
        }

        .position-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .position-meta {
            font-size: 8px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <!-- Repeating Header -->
    <header>
        <div class="header-logo-container">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            @else
                <div style="font-size: 14px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.5px;">GDPS</div>
            @endif
            <div class="company-name">PT Garuda Daya Pratama Sejahtera</div>
        </div>
        <div class="header-info-container">
            <div class="doc-category">Commercial Penawaran / Proposal Costing</div>
            <div class="doc-title">Manpower Costing Sheet</div>
            <div class="doc-subtitle">Template: {{ $record->name }}</div>
        </div>
        <div style="clear: both;"></div>
    </header>

    <!-- Repeating Footer -->
    <footer>
        <div class="footer-left">
            Confidential &mdash; PT Garuda Daya Pratama Sejahtera &bull; Printed on {{ now()->format('d M Y H:i') }} WIB
        </div>
        <div class="footer-right">
            Halaman {PAGE_NUM} dari {PAGE_COUNT}
        </div>
        <div style="clear: both;"></div>
    </footer>

    <!-- Main Container -->
    <div style="width: 100%;">
        @php
            $firstItem = $record->items->first();
            $umkAreaName = $firstItem?->projectArea?->name ?? 'Unspecified Area';
            $umkAmount = 0;
            if ($firstItem && $firstItem->project_area_id && $record->year) {
                $umkAmount = \Modules\MasterData\Models\MinimumWage::where('project_area_id', $firstItem->project_area_id)
                    ->where('year', $record->year)
                    ->first()?->amount ?? 0;
            }
        @endphp

        <!-- Parameters & Project Meta -->
        <table class="parameter-grid">
            <tr>
                <td class="parameter-label">Proposal Lead / Client</td>
                <td class="parameter-val"><strong>{{ $record->lead?->company_name ?? $record->lead?->title ?? '-' }}</strong></td>
                <td class="parameter-label">Costing Scheme Year</td>
                <td class="parameter-val"><strong>{{ $record->year ?? date('Y') }}</strong></td>
            </tr>
            <tr>
                <td class="parameter-label">UMK Region / Area</td>
                <td class="parameter-val">{{ $umkAreaName }}</td>
                <td class="parameter-label">UMK Base Rate</td>
                <td class="parameter-val">Rp {{ number_format($umkAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="parameter-label">Template Code</td>
                <td class="parameter-val"><span style="font-family: monospace;">{{ $record->code ?? '-' }}</span></td>
                <td class="parameter-label">Active Status</td>
                <td class="parameter-val">{{ $record->is_active ? 'ACTIVE' : 'INACTIVE' }}</td>
            </tr>
        </table>

        <!-- Section 1: Executive Summary -->
        <div class="section-title">1. Ringkasan Rekapitulasi Biaya Manpower (Executive Summary)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="text-align: left; width: 25%;">Product Cluster</th>
                    <th style="text-align: left; width: 30%;">Job Position Name</th>
                    <th style="width: 8%;">Qty (Pax)</th>
                    <th style="text-align: right; width: 15%;">Unit Cost / Month</th>
                    <th style="text-align: right; width: 18%;">Line Total / Month</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalQty = 0; 
                    $grandTotalCost = 0;
                @endphp
                @foreach($costSimulation['rows'] as $index => $row)
                    @php
                        $qty = (int)($row['qty'] ?? 0);
                        $totalQty += $qty;
                        $grandTotalCost += $row['line_total'] ?? 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['cluster_name'] ?? 'Unassigned' }}</td>
                        <td class="font-bold">{{ $row['job_position_name'] }} <small style="color: #64748b;">({{ $row['job_position_code'] }})</small></td>
                        <td class="text-center font-bold">{{ number_format($qty) }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($row['unit_cost'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($row['line_total'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if(count($costSimulation['rows']) === 0)
                    <tr>
                        <td colspan="6" class="text-center" style="font-style: italic; color: #94a3b8; padding: 12px 0;">-- No Manpower Items Configured --</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="3" class="text-right uppercase">Total Manpower Pax</td>
                    <td class="text-center font-bold">{{ number_format($totalQty) }} Pax</td>
                    <td colspan="2" class="bg-subtotal"></td>
                </tr>
                <tr class="bg-grand">
                    <td colspan="5" class="text-right uppercase" style="padding: 8px; font-size: 10px;">Grand Total Cost to Company / Bulan (Direct Cost + Fees)</td>
                    <td class="text-right font-mono" style="padding: 8px; font-size: 10px;">Rp {{ number_format($grandTotalCost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Page break to start position details cleanly -->
        <div class="page-break"></div>

        <!-- Section 2: Detailed Sheet per Position -->
        <div class="section-title">2. Detail Rincian Komponen Perhitungan Per Posisi (Detailed Spreadsheet Breakdown)</div>
        
        @foreach($costSimulation['rows'] as $index => $row)
            @php
                $item = $record->items[$index] ?? null;
                $scale = 1 + (($row['scaling_rate'] ?? 0) / 100);

                // Wages
                $gapok = ($row['upah'] - $row['allowances']['fixed']) * $scale;
                $fixedAllowances = $row['allowances']['fixed'] * $scale;
                $nonFixedAllowances = $row['allowances']['non_fixed'] * $scale;
                $subtotalWages = ($row['upah'] + $row['allowances']['non_fixed']) * $scale;

                // Accruals
                $thrBasis = $row['accruals']['basis'] * $scale;
                $thrAccrual = $row['accruals']['thr'] * $scale;
                $compensationAccrual = $row['accruals']['compensation'] * $scale;
                $subtotalAccruals = ($row['accruals']['thr'] + $row['accruals']['compensation']) * $scale;

                // BPJS Kesehatan
                $bpjsHealthBase = ($row['bpjs_health']['base'] ?? 0) * $scale;
                $bpjsHealthEmployer = ($row['bpjs_health']['employer'] ?? 0) * $scale;
                $bpjsHealthEmployee = ($row['bpjs_health']['employee'] ?? 0) * $scale;
                $bpjsHealthTotal = ($row['bpjs_health']['employer_total'] ?? 0) * $scale;

                // BPJS Employment Details
                $jkkBase = ($row['bpjs_employment']['details']['jkk']['base'] ?? 0) * $scale;
                $jkkEmployer = ($row['bpjs_employment']['details']['jkk']['employer'] ?? 0) * $scale;
                $jkkEmployee = ($row['bpjs_employment']['details']['jkk']['employee'] ?? 0) * $scale;

                $jkmBase = ($row['bpjs_employment']['details']['jkm']['base'] ?? 0) * $scale;
                $jkmEmployer = ($row['bpjs_employment']['details']['jkm']['employer'] ?? 0) * $scale;
                $jkmEmployee = ($row['bpjs_employment']['details']['jkm']['employee'] ?? 0) * $scale;

                $jhtBase = ($row['bpjs_employment']['details']['jht']['base'] ?? 0) * $scale;
                $jhtEmployer = ($row['bpjs_employment']['details']['jht']['employer'] ?? 0) * $scale;
                $jhtEmployee = ($row['bpjs_employment']['details']['jht']['employee'] ?? 0) * $scale;

                $jpBase = ($row['bpjs_employment']['details']['jp']['base'] ?? 0) * $scale;
                $jpEmployer = ($row['bpjs_employment']['details']['jp']['employer'] ?? 0) * $scale;
                $jpEmployee = ($row['bpjs_employment']['details']['jp']['employee'] ?? 0) * $scale;

                $subtotalBpjsEmployer = $row['bpjs_total'] * $scale;

                // Tax PPh 21
                $brutoTax = ($row['pph21']['bruto'] ?? 0) * $scale;
                $taxTotal = ($row['pph21']['total'] ?? 0) * $scale;

                // Extra Costs
                $extraCostsTotal = ($row['extra_costs_total'] ?? 0) * $scale;

                // Fees & Grand Totals
                $adminFee = $row['admin_fee'] * $scale;
                $managementFee = $row['management_fee'] * $scale;
                $totalDirectCost = $row['total_direct_cost'] * $scale;
                $unitCost = $row['unit_cost'];
                $lineTotal = $row['line_total'];
            @endphp

            <div class="no-break">
                <div class="position-card">
                    <div class="position-title">{{ $index + 1 }}. {{ $row['job_position_name'] }} <span style="font-weight: normal; color: #475569;">({{ $row['job_position_code'] }})</span></div>
                    <div class="position-meta">
                        Cluster: <strong>{{ $row['cluster_name'] }}</strong> &bull; 
                        Qty: <strong>{{ $row['qty'] }} Pax</strong> &bull; 
                        PTKP Status: <strong>{{ $row['ptkp_status'] }}</strong> &bull; 
                        Contract Type: <strong>{{ $item?->contractType?->name ?? 'N/A' }}</strong> &bull; 
                        Employee Type: <strong>{{ strtoupper($item?->employee_type ?? 'PPU') }}</strong> &bull; 
                        Future Adjustment Scale: <strong>{{ $row['scaling_rate'] }}%</strong>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="text-align: left; width: 35%;">Komponen Biaya (Manpower Cost Components)</th>
                            <th style="text-align: left; width: 22%;">Dasar Perhitungan (Basis)</th>
                            <th style="text-align: right; width: 19%;">Perusahaan (ER Share)</th>
                            <th style="text-align: right; width: 19%;">Karyawan (EE Share)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Wages -->
                        <tr class="bg-subtotal">
                            <td>1</td>
                            <td colspan="4">UPAH & TUNJANGAN (WAGES & SALARY)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Gaji Pokok (Basic Salary)</td>
                            <td>Project UMK Base</td>
                            <td class="text-right font-mono">Rp {{ number_format($gapok, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Tunjangan Tetap (Fixed Allowances)</td>
                            <td>Standard Allowances</td>
                            <td class="text-right font-mono">Rp {{ number_format($fixedAllowances, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Tunjangan Non-Tetap (Non-Fixed Allowances)</td>
                            <td>Variables Allowances</td>
                            <td class="text-right font-mono">Rp {{ number_format($nonFixedAllowances, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr class="bg-total">
                            <td></td>
                            <td colspan="2">Subtotal Upah & Tunjangan (A)</td>
                            <td class="text-right font-mono">Rp {{ number_format($subtotalWages, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>

                        <!-- 2. Accruals -->
                        <tr class="bg-subtotal">
                            <td>2</td>
                            <td colspan="4">AKRUAL & AKUMULASI TAHUNAN (ACCRUALS & PROVISIONS)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>THR (Tunjangan Hari Raya) - <small style="color: #64748b;">Method: {{ str_replace('_', ' ', $item?->thr_billing_method ?? 'monthly_accrual') }}</small></td>
                            <td class="font-mono">Basis: Rp {{ number_format($thrBasis, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($thrAccrual, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Kompensasi Kerja (Job Compensation) - <small style="color: #64748b;">Method: {{ str_replace('_', ' ', $item?->compensation_billing_method ?? 'monthly_accrual') }}</small></td>
                            <td class="font-mono">Basis: Rp {{ number_format($totalUpahScaled, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($compensationAccrual, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr class="bg-total">
                            <td></td>
                            <td colspan="2">Subtotal Akrual Manpower (B)</td>
                            <td class="text-right font-mono">Rp {{ number_format($subtotalAccruals, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>

                        <!-- 3. BPJS -->
                        <tr class="bg-subtotal">
                            <td>3</td>
                            <td colspan="4">KONTRIBUSI BPJS (BPJS SOCIAL SECURITY CONTRIBUTIONS)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>BPJS Kesehatan (Health Insurance)</td>
                            <td class="font-mono">Basis: Rp {{ number_format($bpjsHealthBase, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($bpjsHealthEmployer, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['bpjs_health']['employer_rate'] ?? 0.04) * 100 }}%</small>
                            </td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($bpjsHealthEmployee, 0, ',', '.') }}
                                <br><small class="{{ $item?->is_employee_jkn_borne_by_company ? 'badge-borne' : 'badge-deduct' }}">
                                    {{ $item?->is_employee_jkn_borne_by_company ? '(Company Borne)' : '(Deduct Salary)' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>BPJS Ketenagakerjaan JKK (Work Injury)</td>
                            <td class="font-mono">Basis: Rp {{ number_format($jkkBase, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jkkEmployer, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['bpjs_employment']['details']['jkk']['rate'] ?? ($row['bpjs_employment']['details']['jkk']['employer_rate'] ?? 0)) * 100 }}%</small>
                            </td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>BPJS Ketenagakerjaan JKM (Death Benefit)</td>
                            <td class="font-mono">Basis: Rp {{ number_format($jkmBase, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jkmEmployer, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['bpjs_employment']['details']['jkm']['rate'] ?? ($row['bpjs_employment']['details']['jkm']['employer_rate'] ?? 0)) * 100 }}%</small>
                            </td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>BPJS Ketenagakerjaan JHT (Old Age Pension)</td>
                            <td class="font-mono">Basis: Rp {{ number_format($jhtBase, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jhtEmployer, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['bpjs_employment']['details']['jht']['employer_rate'] ?? 0.037) * 100 }}%</small>
                            </td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jhtEmployee, 0, ',', '.') }}
                                <br><small class="{{ $item?->is_employee_jht_borne_by_company ? 'badge-borne' : 'badge-deduct' }}">
                                    {{ $item?->is_employee_jht_borne_by_company ? '(Company Borne)' : '(Deduct Salary)' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>BPJS Ketenagakerjaan JP (Pension Plan)</td>
                            <td class="font-mono">Basis: Rp {{ number_format($jpBase, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jpEmployer, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['bpjs_employment']['details']['jp']['employer_rate'] ?? 0.02) * 100 }}%</small>
                            </td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($jpEmployee, 0, ',', '.') }}
                                <br><small class="{{ $item?->is_employee_jp_borne_by_company ? 'badge-borne' : 'badge-deduct' }}">
                                    {{ $item?->is_employee_jp_borne_by_company ? '(Company Borne)' : '(Deduct Salary)' }}
                                </small>
                            </td>
                        </tr>
                        <tr class="bg-total">
                            <td></td>
                            <td colspan="2">Subtotal Jaminan Sosial & BPJS Perusahaan (C)</td>
                            <td class="text-right font-mono">Rp {{ number_format($subtotalBpjsEmployer, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>

                        <!-- 4. Taxes -->
                        <tr class="bg-subtotal">
                            <td>4</td>
                            <td colspan="4">PERHITUNGAN PAJAK PPH 21 (INCOME TAX PPH 21)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>PPh Pasal 21 Bulanan - <small style="color: #64748b;">Method: {{ $item?->use_ter_method ? 'TER Method' : 'Progressive Psl 17' }}</small></td>
                            <td class="font-mono">Bruto: Rp {{ number_format($brutoTax, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">
                                Rp {{ number_format($taxTotal, 0, ',', '.') }}
                                <br><small style="color: #64748b;">Rate: {{ ($row['pph21']['rate'] ?? 0) * 100 }}%</small>
                            </td>
                            <td class="text-right font-mono">
                                @if(! $item?->is_tax_borne_by_company)
                                    Rp {{ number_format($taxTotal, 0, ',', '.') }}
                                @else
                                    Rp 0
                                @endif
                                <br><small class="{{ $item?->is_tax_borne_by_company ? 'badge-borne' : 'badge-deduct' }}">
                                    {{ $item?->is_tax_borne_by_company ? '(Company Borne)' : '(Deduct Salary)' }}
                                </small>
                            </td>
                        </tr>
                        <tr class="bg-total">
                            <td></td>
                            <td colspan="2">Subtotal Pajak PPh 21 Perusahaan (D)</td>
                            <td class="text-right font-mono">Rp {{ number_format($taxTotal, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>

                        <!-- 5. Extra Costs -->
                        @if(! empty($row['extra_costs']))
                            <tr class="bg-subtotal">
                                <td>5</td>
                                <td colspan="4">BIAYA OPERASIONAL EKSTRA (EXTRA MANPOWER COSTS)</td>
                            </tr>
                            @foreach($row['extra_costs'] as $ec)
                                <tr>
                                    <td></td>
                                    <td>{{ $ec['name'] ?? 'Unnamed Cost' }}</td>
                                    <td>Nominal Custom</td>
                                    <td class="text-right font-mono">Rp {{ number_format(($ec['value'] ?? $ec['amount'] ?? 0) * $scale, 0, ',', '.') }}</td>
                                    <td class="text-center">-</td>
                                </tr>
                            @endforeach
                            <tr class="bg-total">
                                <td></td>
                                <td colspan="2">Subtotal Biaya Ekstra Perusahaan (E)</td>
                                <td class="text-right font-mono">Rp {{ number_format($extraCostsTotal, 0, ',', '.') }}</td>
                                <td class="text-center">-</td>
                            </tr>
                        @endif

                        <!-- Summary Fees & Management -->
                        <tr class="bg-subtotal">
                            <td>6</td>
                            <td colspan="4">ADMINISTRATION FEES & BILLING SUMMARY</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Management Fee (Flat)</td>
                            <td>Fixed Management Charge</td>
                            <td class="text-right font-mono">Rp {{ number_format($managementFee, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Management / Admin Fee (Percentage)</td>
                            <td>Basis: Direct Cost</td>
                            <td class="text-right font-mono">Rp {{ number_format($adminFee, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr class="bg-total">
                            <td></td>
                            <td colspan="2">Total Direct Cost / Bulan (A + B + C + D + E)</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($totalDirectCost, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr class="bg-grand">
                            <td></td>
                            <td colspan="2">UNIT BILLING / MONTH (COST TO COMPANY PER PAX)</td>
                            <td class="text-right font-mono font-bold" style="font-size: 10px;">Rp {{ number_format($unitCost, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr class="bg-grand">
                            <td></td>
                            <td colspan="2">LINE TOTAL BILLING / MONTH (QTY * UNIT BILLING)</td>
                            <td class="text-right font-mono font-bold" style="font-size: 10px;">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Page break between detailed position sheets -->
            @if(! $loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach

    </div>

    <!-- Script to render actual dynamic page numbers -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 7.5;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $pdf->page_text($pdf->get_width() - 40 - $width, $pdf->get_height() - 32, $text, $font, $size, array(0.39, 0.45, 0.55));
        }
    </script>
</body>

</html>
