<!DOCTYPE html>
<html lang="en">
@php
    $formatMoney = function ($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    };
@endphp

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Project Information - {{ $record->project?->name }}</title>
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

        .font-bold {
            font-weight: 700;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .italic {
            font-style: italic;
        }

        .section-header {
            background-color: #f1f5f9;
            font-weight: 800;
            padding: 8px 12px;
            margin-top: 20px;
            border-left: 4px solid #2563eb;
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

        .signature-box {
            text-align: center;
            vertical-align: top;
            padding: 10px;
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
                <h1>PROJECT INFORMATION</h1>
                <p>{{ $record->project?->project_code ?? 'PENDING' }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ now()->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            {{-- General Information --}}
            <div class="section-header">I. GENERAL INFORMATION</div>
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 25%; color: #64748b;">Project Name</td>
                    <td style="border: none; width: 25%; font-weight: bold;">{{ $record->project?->name }}</td>
                    <td style="border: none; width: 25%; color: #64748b;">Process Date</td>
                    <td style="border: none; width: 25%; font-weight: bold;">
                        {{ $record->process_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Project Type</td>
                    <td style="border: none; font-weight: bold;">{{ $record->projectType?->name }}</td>
                    <td style="border: none; color: #64748b;">Status</td>
                    <td style="border: none; font-weight: bold;">{{ $record->status?->getLabel() }}</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Start Date</td>
                    <td style="border: none; font-weight: bold;">{{ $record->start_date?->format('d M Y') }}</td>
                    <td style="border: none; color: #64748b;">End Date</td>
                    <td style="border: none; font-weight: bold;">{{ $record->end_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Customer</td>
                    <td style="border: none; font-weight: bold;">{{ $record->project?->customer?->name }}</td>
                    <td style="border: none; color: #64748b;">Previous Code</td>
                    <td style="border: none; font-weight: bold;">{{ $record->previous_code ?? '-' }}</td>
                </tr>
            </table>

            {{-- Operational details --}}
            <div class="section-header">II. OPERATIONAL DETAILS</div>
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 25%; color: #64748b;">Visit Schedule</td>
                    <td style="border: none; width: 25%; font-weight: bold;">
                        {{ $record->operational_visit_schedule ?? '-' }}</td>
                    <td style="border: none; width: 25%; color: #64748b;">Cut Off Date</td>
                    <td style="border: none; width: 25%; font-weight: bold;">
                        {{ $record->bapp_cut_off_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Billing Option</td>
                    <td style="border: none; font-weight: bold;">{{ $record->billingOption?->name ?? '-' }}</td>
                    <td style="border: none; color: #64748b;">Max Invoice Delivery</td>
                    <td style="border: none; font-weight: bold;">
                        {{ $record->max_invoice_send_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">OPREP Name</td>
                    <td style="border: none; font-weight: bold;">{{ $record->oprep?->name ?? '-' }}</td>
                    <td style="border: none; color: #64748b;">AMS Name</td>
                    <td style="border: none; font-weight: bold;">{{ $record->ams?->name ?? '-' }}</td>
                </tr>
            </table>

            {{-- Financial Summary --}}
            <div class="section-header">III. FINANCIAL SUMMARY</div>
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 25%; color: #64748b;">Monthly Revenue</td>
                    <td style="border: none; width: 25%; font-weight: bold;">
                        {{ $formatMoney($record->revenue_per_month) }}</td>
                    <td style="border: none; width: 25%; color: #64748b;">Monthly Direct Cost</td>
                    <td style="border: none; width: 25%; font-weight: bold;">{{ $formatMoney($record->direct_cost) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Management Fee</td>
                    <td style="border: none; font-weight: bold;">{{ $formatMoney($record->management_fee_per_month) }}
                    </td>
                    <td style="border: none; color: #64748b;">VAT Percentage</td>
                    <td style="border: none; font-weight: bold;">{{ $record->ppn_percentage }}%</td>
                </tr>
                <tr>
                    <td style="border: none; color: #64748b;">Payment Term</td>
                    <td style="border: none; font-weight: bold;" colspan="3">
                        {{ $record->paymentTerm?->name ?? '-' }}</td>
                </tr>
            </table>

            {{-- Materials & Manpower Details --}}
            @if (!empty($record->analysis_details))
                <div class="section-header">IV. ANALYSIS DETAILS (MATERIALS & MANPOWER)</div>
                @foreach ($record->analysis_details as $categoryId => $items)
                    @php
                        $category = \Modules\MasterData\Models\ItemCategory::find($categoryId);
                        $categoryName = $category?->name ?? 'Miscellaneous';
                    @endphp
                    <div
                        style="font-weight: bold; margin-top: 10px; color: #2563eb; font-size: 9px; text-transform: uppercase;">
                        {{ $categoryName }} Details</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th style="width: 15%; text-align: center;">Quantity</th>
                                <th style="width: 20%; text-align: right;">Price</th>
                                <th style="width: 25%; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $masterItem = \Modules\MasterData\Models\Item::find($item['item_id']);
                                    $qty = $item['quantity'] ?? 0;
                                    $price = $item['price'] ?? 0;
                                    $total = $qty * $price;
                                @endphp
                                <tr>
                                    <td>{{ $masterItem?->name ?? 'Unknown Item' }}</td>
                                    <td class="text-center">{{ $qty }}</td>
                                    <td class="text-right">{{ $formatMoney($price) }}</td>
                                    <td class="text-right">{{ $formatMoney($total) }}</td>
                                </tr>
                                @if (!empty($item['notes']))
                                    <tr>
                                        <td colspan="4" style="color: #64748b; font-size: 8px; padding-left: 20px;">
                                            Note: {{ $item['notes'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif

            {{-- Remuneration Details --}}
            @if (!empty($record->remuneration_details))
                <div class="section-header">V. REMUNERATION DETAILS</div>
                <table style="border: none; margin-bottom: 5px;">
                    <tr>
                        <td style="border: none; width: 25%; color: #64748b;">TAD Payroll Date</td>
                        <td style="border: none; width: 25%; font-weight: bold;">
                            {{ $record->payroll_date?->day ?? '-' }}</td>
                        <td style="border: none; width: 25%; color: #64748b;">Overtime Cut Off</td>
                        <td style="border: none; width: 25%; font-weight: bold;">
                            {{ $record->overtime_cut_off_date?->day ?? '-' }}</td>
                    </tr>
                </table>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Component Name</th>
                            <th style="width: 20%; text-align: right;">Amount</th>
                            <th style="width: 30%;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record->remuneration_details as $rem)
                            <tr>
                                <td>{{ $rem['component_name'] }}</td>
                                <td class="text-right">{{ $formatMoney($rem['amount'] ?? 0) }}</td>
                                <td>{{ $rem['notes'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Signatures --}}
            @php
                $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
                $rules = $signatureService->getRequiredApprovers($record);
                $signaturesData = $record->signatures ?? []; // Assuming this is stored as array in model
            @endphp

            @if ($rules->isNotEmpty())
                <div class="section-header">VI. APPROVALS</div>
                <table style="width: 100%; border: none; margin-top: 20px;">
                    <tr>
                        @foreach ($rules as $rule)
                            @php
                                $matchingSignature = null;
                                foreach ($signaturesData as $sig) {
                                    if (
                                        $sig['type'] === $rule->signature_type &&
                                        $sig['user_role'] ===
                                            (is_array($rule->approver_role)
                                                ? $rule->approver_role[0]
                                                : $rule->approver_role)
                                    ) {
                                        $matchingSignature = $sig;
                                        break;
                                    }
                                }
                            @endphp
                            <td class="signature-box" style="width: {{ 100 / $rules->count() }}%;">
                                <p
                                    style="font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 10px;">
                                    {{ $rule->signature_type ?: 'Approval' }}
                                </p>

                                @if ($matchingSignature)
                                    @php
                                        // Since we don't have the user object here easily,
// we might need to fetch it or use the data stored in the json
$user = \App\Models\User::find($matchingSignature['user_id']);
$qrCode = null;
if ($user) {
    $qrUrl = $signatureService->createSignatureData(
        $user,
        $record,
        $matchingSignature['type'] ?? 'approved',
                                            );
                                            $qrCode = $signatureService->generateQRCode($qrUrl);
                                        }
                                    @endphp
                                    @if ($qrCode)
                                        <div style="margin-bottom: 5px;">
                                            <img src="{{ $qrCode }}" style="width: 60px; height: 60px;">
                                        </div>
                                    @endif
                                    <div style="border-top: 1px solid #000; padding-top: 4px;">
                                        <p
                                            style="font-size: 9px; font-weight: 800; text-transform: uppercase; margin: 0;">
                                            {{ $matchingSignature['user_name'] }}
                                        </p>
                                        <p
                                            style="font-size: 6px; color: #64748b; text-transform: uppercase; margin: 1px 0 0 0;">
                                            {{ $matchingSignature['user_role'] }}
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
