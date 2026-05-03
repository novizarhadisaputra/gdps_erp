@use(Modules\MasterData\Services\SignatureService)
<!DOCTYPE html>
<html lang="en">
@php
    $formatMoney = function ($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    };
@endphp

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Profitability Analysis Summary - {{ $record->number }}</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 10px;
            vertical-align: middle;
        }

        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-black {
            font-weight: 900;
        }

        .font-bold {
            font-weight: 700;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .italic {
            font-style: italic;
        }

        .section-row {
            background-color: #f1f5f9;
            font-weight: 800;
        }

        .revenue-row {
            background-color: #eff6ff;
            color: #1e3a8a;
        }

        .profit-row {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .net-profit-row {
            background-color: #0f172a;
            color: white !important;
        }

        .signature-box {
            width: 25%;
            border: none;
            text-align: center;
            vertical-align: bottom;
            padding: 0 5px;
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
                <h1>PROFITABILITY ANALYSIS</h1>
                <p>{{ $record->number }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ now()->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            {{-- Customer Info Table --}}
            <table style="width: 100%; border: none; margin-bottom: 20px;">
                <tr>
                    <td style="width: 50%; border: none; padding-right: 15px; vertical-align: top;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">
                                    Customer</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 10px; text-align: right; font-weight: 900;">
                                    {{ $record->customer?->name }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">
                                    Work Scheme</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 10px; text-align: right; font-weight: bold;">
                                    {{ $record->workScheme?->name }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; border: none; padding-left: 15px; vertical-align: top;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">
                                    Project Ref</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 10px; text-align: right; font-weight: bold;">
                                    {{ $record->project?->number ?? 'PENDING' }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">
                                    Status</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 0; font-size: 10px; text-align: right; font-weight: bold;">
                                    {{ $record->status->getLabel() }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Financial Data Table --}}
            <table>
                <thead>
                    <tr>
                        <th style="width: 55%;">Particulars / Description</th>
                        <th style="width: 15%; text-align: center;">Metrics</th>
                        <th style="width: 30%; text-align: right;">Amount (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- REVENUE --}}
                    <tr class="revenue-row">
                        <td class="font-bold">I. Total Revenue</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold">{{ $formatMoney($record->revenue_per_month) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;" class="italic">Base Project Fee / Price</td>
                        <td class="text-center">-</td>
                        <td class="text-right">{{ $formatMoney((float)$record->revenue_per_month - (float)$record->management_fee) }}
                        </td>
                    </tr>
                    @if ($record->management_fee > 0)
                        <tr>
                            <td style="padding-left: 30px;" class="italic">Management Fee</td>
                            <td class="text-center">{{ number_format($record->management_fee_rate, 2) }}%</td>
                            <td class="text-right">{{ $formatMoney($record->management_fee) }}</td>
                        </tr>
                    @endif

                    {{-- DIRECT COSTS --}}
                    <tr class="section-row">
                        <td class="font-bold">II. Direct Operating Costs</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold">({{ $formatMoney($record->direct_cost) }})</td>
                    </tr>
                    @php
                        $directCosts = $record->getDirectItems()->groupBy('direct_cost_category_id');
                    @endphp
                    @foreach ($directCosts as $categoryId => $items)
                        @php $category = $items->first()->category; @endphp
                        <tr>
                            <td style="padding-left: 30px;">{{ $category?->name ?? 'Miscellaneous' }}</td>
                            <td class="text-center">
                                {{ $category?->name === 'Manpower' ? (int) $items->sum('quantity') . ' HC' : '-' }}
                            </td>
                            <td class="text-right">{{ $formatMoney($items->sum(fn($i) => (float)($i->total_monthly_cost ?? 0))) }}</td>
                        </tr>
                    @endforeach

                    {{-- GROSS PROFIT --}}
                    <tr class="profit-row">
                        <td class="font-bold">III. Gross Profit / Margin</td>
                        <td class="text-center">{{ number_format($record->margin_percentage, 2) }}%</td>
                        <td class="text-right font-bold">
                            {{ $formatMoney((float)$record->revenue_per_month - (float)$record->direct_cost) }}
                        </td>
                    </tr>

                    {{-- INDIRECT COSTS --}}
                    <tr class="section-row">
                        <td class="font-bold">IV. Indirect & Overhead Costs</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold">
                            ({{ $formatMoney($record->getTotalIndirectCost()) }})
                        </td>
                    </tr>
                    @php
                        $indirectItems = $record->getIndirectItems();
                    @endphp
                    @foreach ($indirectItems as $item)
                        <tr>
                            <td style="padding-left: 30px;" class="italic">{{ $item->category?->name ?? 'Indirect' }}
                            </td>
                            <td class="text-center">
                                @if (($item->calculation_type ?? 'fixed') === 'percentage')
                                    {{ (float) ($item->total_monthly_cost ?? $item->unit_cost_price ?? 0) }}% of {{ $item->percentage_basis ?? 'rev' }}
                                @else
                                    Fixed
                                @endif
                            </td>
                            <td class="text-right">
                                @php
                                    $val = (float) ($item->total_monthly_cost ?? $item->unit_cost_price ?? 0);
                                    $finalVal = $val;
                                    if (($item->calculation_type ?? 'fixed') === 'percentage') {
                                        $basis = $item->percentage_basis ?? 'revenue';
                                        $basisValue = $basis === 'revenue' ? (float) $record->revenue_per_month : (float) $record->direct_cost;
                                        $finalVal = $basisValue * ($val / 100);
                                    }
                                @endphp
                                {{ $formatMoney($finalVal) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- EBITDA --}}
                    <tr style="background-color: #fffbeb;">
                        <td class="font-bold">V. EBITDA</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold" style="color: #d97706;">{{ $formatMoney($record->ebitda) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="italic text-gray-400">Depreciation & Amortization</td>
                        <td class="text-center">-</td>
                        <td class="text-right">
                            ({{ $formatMoney((float)$record->depreciation + (float)$record->manual_depreciation) }})</td>
                    </tr>

                    {{-- EBIT --}}
                    <tr style="background-color: #f9fafb;">
                        <td class="font-bold uppercase" style="font-size: 9px;">EBIT (Earnings Before Interest & Tax)
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold">{{ $formatMoney($record->ebit) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;" class="italic">Finance Cost / Project Interest</td>
                        <td class="text-center">{{ number_format($record->interest_rate, 2) }}%</td>
                        <td class="text-right">({{ $formatMoney((float)$record->ebit - (float)$record->ebt) }})</td>
                    </tr>

                    {{-- EBT --}}
                    <tr style="background-color: #f9fafb;" class="font-bold">
                        <td class="uppercase" style="font-size: 9px;">EBT (Earnings Before Tax)</td>
                        <td class="text-center">-</td>
                        <td class="text-right font-bold">{{ $formatMoney($record->ebt) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 30px;" class="italic">Corporate Income Tax (Est.)</td>
                        <td class="text-center">{{ number_format($record->tax_rate, 2) }}%</td>
                        <td class="text-right">({{ $formatMoney((float)$record->ebt - (float)$record->net_profit) }})</td>
                    </tr>

                    {{-- NET PROFIT --}}
                    <tr class="net-profit-row">
                        <td style="font-size: 16px; font-weight: 900; letter-spacing: 2px;">NET PROFIT</td>
                        <td class="text-center">
                            <div style="font-size: 7px; color: #94a3b8; margin-bottom: 2px;">RATIO</div>
                            <div style="font-size: 12px; font-weight: 900;">
                                {{ number_format($record->net_profit_margin, 2) }}%
                            </div>
                        </td>
                        <td class="text-right" style="font-size: 18px; font-weight: 900;">
                            {{ $formatMoney($record->net_profit) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Signatures --}}
            @php
                $signatureService = app(SignatureService::class);
                $rules = $signatureService->getRequiredApprovers($record);
                $signatures = $record->signatures;
                $marginSignature = $signatures->firstWhere('signature_type', 'MarginApproval');
                $otherSignatures = $signatures->where('signature_type', '!=', 'MarginApproval');
                
                $totalRules = $rules->count() + ($marginSignature ? 1 : 0);
            @endphp

            @if ($totalRules > 0)
                <table style="width: 100%; border: none; margin-top: 50px;">
                    <tr>
                        {{-- Margin Approval --}}
                        @if ($marginSignature)
                            <td class="signature-box"
                                style="text-align: center; vertical-align: top; width: {{ 100 / $totalRules }}%;">
                                <p
                                    style="font-size: 7px; font-weight: 800; color: #2563eb; text-transform: uppercase; margin-bottom: 10px;">
                                    Margin Approval
                                </p>
                                @php
                                    $qrUrl = $signatureService->createSignatureData(
                                        $marginSignature->user,
                                        $record,
                                        'MarginApproval',
                                    );
                                    $qrCode = $signatureService->generateQRCode($qrUrl);
                                @endphp
                                <div style="margin-bottom: 5px;">
                                    <img src="{{ $qrCode }}" style="width: 60px; height: 60px;">
                                </div>
                                <div style="border-top: 1px solid #2563eb; padding-top: 4px;">
                                    <p
                                        style="font-size: 9px; font-weight: 800; text-transform: uppercase; margin: 0;">
                                        {{ $marginSignature->user->name }}
                                    </p>
                                    <p
                                        style="font-size: 6px; color: #64748b; text-transform: uppercase; margin: 1px 0 0 0;">
                                        {{ $marginSignature->role }}
                                    </p>
                                </div>
                            </td>
                        @endif

                        {{-- Final Approals --}}
                        @foreach ($rules as $rule)
                            @php
                                $matchingSignature = $otherSignatures->first(function ($sig) use ($rule, $signatureService) {
                                    return $signatureService->isEligibleApprover($rule, $sig->user);
                                });
                            @endphp
                            <td class="signature-box"
                                style="text-align: center; vertical-align: top; width: {{ 100 / $totalRules }}%;">
                                <p
                                    style="font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 10px;">
                                    {{ $rule->signature_type ?: 'Final Approval' }}
                                </p>

                                @if ($matchingSignature)
                                    @php
                                        $qrUrl = $signatureService->createSignatureData(
                                            $matchingSignature->user,
                                            $record,
                                            $matchingSignature->signature_type ?? 'approved',
                                        );
                                        $qrCode = $signatureService->generateQRCode($qrUrl);
                                    @endphp
                                    <div style="margin-bottom: 5px;">
                                        <img src="{{ $qrCode }}" style="width: 60px; height: 60px;">
                                    </div>
                                    <div style="border-top: 1px solid #000; padding-top: 4px;">
                                        <p
                                            style="font-size: 9px; font-weight: 800; text-transform: uppercase; margin: 0;">
                                            {{ $matchingSignature->user->name }}
                                        </p>
                                        <p
                                            style="font-size: 6px; color: #64748b; text-transform: uppercase; margin: 1px 0 0 0;">
                                            {{ $matchingSignature->role }}
                                        </p>
                                    </div>
                                @else
                                    <div style="height: 60px; margin-bottom: 5px; border: 1px dashed #e2e8f0;"></div>
                                    <div style="border-top: 1px solid #e2e8f0; padding-top: 4px;">
                                        <p
                                            style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin: 0;">
                                            @if ($rule->approver_type === 'Role')
                                                {{ is_array($rule->approver_role) ? implode(' / ', $rule->approver_role) : $rule->approver_role }}
                                            @elseif($rule->approver_type === 'Position')
                                                {{ is_array($rule->approver_position) ? implode(' / ', $rule->approver_position) : $rule->approver_position }}
                                            @else
                                                {{ $rule->approver_type }}
                                            @endif
                                        </p>
                                        <p
                                            style="font-size: 6px; color: #cbd5e1; text-transform: uppercase; margin: 1px 0 0 0;">
                                            Garuda Daya Pratama Sejahtera
                                        </p>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endif
        </div> {{-- End content --}}

        <div class="footer">
            PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Digital Document Verification System
        </div>
    </div> {{-- End container --}}
</body>

</html>
