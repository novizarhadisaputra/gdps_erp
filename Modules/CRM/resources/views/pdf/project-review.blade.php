<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Project Review - {{ $record->lead?->customer?->name ?? 'N/A' }}</title>
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
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 2px solid #eff6ff;
            padding-bottom: 8px;
            margin-top: 30px;
            display: block;
        }

        .module-badge {
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
        }

        .badge-gi { background-color: #eff6ff; color: #2563eb; }
        .badge-pa { background-color: #ecfdf5; color: #059669; }
        .badge-proposal { background-color: #fffbeb; color: #d97706; }

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

        .grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .col {
            width: 50%;
            padding: 0 10px;
        }

        .metric-card {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .metric-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
        }

        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 25%;
            text-align: center;
            padding: 15px;
            vertical-align: top;
        }

        .signature-role {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 10px;
            min-height: 20px;
        }

        .signature-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 10px;
        }

        .qr-code img {
            width: 60px;
            height: 60px;
            margin: 5px auto;
        }

        .signed-date {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 20px 50px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }

        .doc-tag {
            font-size: 7px;
            font-weight: 900;
            padding: 1px 4px;
            border-radius: 3px;
            margin-top: 4px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="float: left; width: 60%;">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
                <div style="font-size: 10px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px;">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Digital Enterprise Resource Planning System</div>
            </div>
            <div class="document-type" style="float: right; width: 40%;">
                <h1 style="font-size: 18px;">PROJECT REVIEW</h1>
                <p style="color: #2563eb; font-weight: bold;">{{ $record->number }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 8px;">
                    Date: {{ now()->format('d M Y') }}<br>
                    REF: {{ $record->lead?->reference_no ?? 'N/A' }}
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            {{-- Project Context --}}
            <div class="section-title">Project Overview</div>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td class="info-value" colspan="3">{{ $record->lead?->customer?->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Project Concept</th>
                    <td colspan="3">{{ $record->lead?->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Review Status</th>
                    <td><span class="info-value">{{ $record->status?->getLabel() ?? 'Unknown' }}</span></td>
                    <th>Revision No.</th>
                    <td><span class="info-value">{{ $record->revision_number ?? 0 }}</span></td>
                </tr>
            </table>

            {{-- GI Summary --}}
            <div class="section-title">General Information <span class="module-badge badge-gi">Module GI</span></div>
            @if($record->generalInformation)
            <table>
                <tr>
                    <th>Document Number</th>
                    <td class="info-value">{{ $record->generalInformation->number }}</td>
                </tr>
                <tr>
                    <th>Customer Area</th>
                    <td>{{ $record->generalInformation->projectArea?->name ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Estimated Period</th>
                    <td class="info-value">
                        {{ $record->generalInformation->estimated_start_date?->format('d/m/Y') }} —
                        {{ $record->generalInformation->estimated_end_date?->format('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <th>Work Scheme</th>
                    <td>{{ $record->generalInformation->workScheme?->name ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Current Status</th>
                    <td><span style="color: #2563eb; font-weight: bold;">{{ $record->generalInformation->status?->getLabel() ?: 'N/A' }}</span></td>
                </tr>
            </table>
            @else
            <div style="padding: 20px; text-align: center; border: 1px dashed #e2e8f0; color: #94a3b8; font-style: italic; border-radius: 8px;">
                No General Information document linked to this review.
            </div>
            @endif

            {{-- PA Summary --}}
            <div class="section-title">Profitability Analysis <span class="module-badge badge-pa">Module PA</span></div>
            @if($record->profitabilityAnalysis)
            <table style="margin-bottom: 10px;">
                <tr>
                    <td style="width: 33%; border: none; padding: 0 5px 0 0;">
                        <div class="metric-card">
                            <div class="metric-label">Monthly Revenue</div>
                            <div class="metric-value">@money($record->profitabilityAnalysis->revenue_per_month, 'IDR')</div>
                        </div>
                    </td>
                    <td style="width: 33%; border: none; padding: 0 5px;">
                        <div class="metric-card">
                            <div class="metric-label">EBITDA</div>
                            <div class="metric-value">@money($record->profitabilityAnalysis->ebitda, 'IDR')</div>
                        </div>
                    </td>
                    <td style="width: 34%; border: none; padding: 0 0 0 5px;">
                        <div class="metric-card">
                            <div class="metric-label">Net Margin</div>
                            <div class="metric-value" style="color: #059669;">{{ number_format($record->profitabilityAnalysis->net_profit_margin, 2) }}%</div>
                        </div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <th>Document Number</th>
                    <td class="info-value">{{ $record->profitabilityAnalysis->number }}</td>
                    <th>Margin Approved</th>
                    <td class="info-value" style="{{ $record->profitabilityAnalysis->is_margin_approved ? 'color: #059669;' : 'color: #d97706;' }}">
                        {{ $record->profitabilityAnalysis->is_margin_approved ? 'YES' : 'PENDING' }}
                    </td>
                </tr>
                <tr>
                    <th>Service Fee (%)</th>
                    <td>{{ number_format($record->profitabilityAnalysis->service_fee_percent, 2) }}%</td>
                    <th>Status</th>
                    <td class="info-value">{{ $record->profitabilityAnalysis->status?->getLabel() ?: 'N/A' }}</td>
                </tr>
            </table>
            @else
            <div style="padding: 20px; text-align: center; border: 1px dashed #e2e8f0; color: #94a3b8; font-style: italic; border-radius: 8px;">
                No Profitability Analysis document linked to this review.
            </div>
            @endif

            {{-- Proposal Summary --}}
            <div class="section-title">Sales Proposal <span class="module-badge badge-proposal">Module Proposal</span></div>
            @if($record->proposal)
            <table>
                <tr>
                    <th>Proposal Number</th>
                    <td class="info-value">{{ $record->proposal->number }}</td>
                </tr>
                <tr>
                    <th>Project Title</th>
                    <td>{{ $record->proposal->title }}</td>
                </tr>
                <tr>
                    <th>Total Proposal Value</th>
                    <td class="info-value" style="font-size: 14px; color: #d97706;">@money($record->proposal->amount, 'IDR')</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span style="color: #d97706; font-weight: bold;">{{ $record->proposal->status?->getLabel() ?: 'N/A' }}</span></td>
                </tr>
            </table>
            @else
            <div style="padding: 20px; text-align: center; border: 1px dashed #e2e8f0; color: #94a3b8; font-style: italic; border-radius: 8px;">
                No Sales Proposal linked to this review.
            </div>
            @endif

            {{-- Signatures --}}
            <div class="section-title">Authorization Summary</div>
            @php
                $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
                $allSignatures = collect();
                if ($record->generalInformation) {
                    $allSignatures = $allSignatures->merge($record->generalInformation->signatures->map(fn($s) => ['type' => 'GI', 'sig' => $s]));
                }
                if ($record->profitabilityAnalysis) {
                    $allSignatures = $allSignatures->merge($record->profitabilityAnalysis->signatures->map(fn($s) => ['type' => 'PA', 'sig' => $s]));
                }
                if ($record->proposal) {
                    $allSignatures = $allSignatures->merge($record->proposal->signatures->map(fn($s) => ['type' => 'Proposal', 'sig' => $s]));
                }
                $uniqueSignatures = $allSignatures->groupBy(fn($item) => $item['sig']->user_id . '_' . $item['sig']->signature_type);
            @endphp

            @if($uniqueSignatures->isNotEmpty())
                <table style="border: none; margin-top: 20px;">
                    @foreach($uniqueSignatures->chunk(4) as $chunk)
                        <tr style="border: none;">
                            @foreach($chunk as $group)
                                @php
                                    $first = $group->first();
                                    $sig = $first['sig'];
                                    $qrUrl = $signatureService->createSignatureData($sig->user, $sig->signable, $sig->signature_type ?: 'approved');
                                    $qrCodeUri = $signatureService->generateQRCode($qrUrl);
                                @endphp
                                <td class="signature-box" style="border: none;">
                                    <div class="signature-role">{{ $sig->signature_type ?: 'Manual Approval' }}</div>
                                    <div class="qr-code">
                                        <img src="{{ $qrCodeUri }}" />
                                    </div>
                                    <div class="signature-name">{{ $sig->user->name }}</div>
                                    <div class="signed-date">{{ $sig->signed_at->format('d M Y H:i') }}</div>
                                    <div style="margin-top: 5px;">
                                        @foreach($group->pluck('type')->unique() as $docType)
                                            <span class="doc-tag {{ match($docType) { 'GI' => 'badge-gi', 'PA' => 'badge-pa', 'Proposal' => 'badge-proposal', default => '' } }}">
                                                {{ $docType }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 4; $i++)
                                <td class="signature-box" style="border: none;"></td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            @else
            <div style="padding: 40px; text-align: center; border: 2px dashed #f1f5f9; color: #94a3b8; border-radius: 12px; margin-top: 20px;">
                <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px;">Pending Authorization</div>
                <div style="font-size: 8px; margin-top: 5px;">No digital signatures recorded for this review chain.</div>
            </div>
            @endif
        </div>

        <div class="footer">
            PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Digital Document Verification System &nbsp;&bull;&nbsp; Page 1 of 1
        </div>
    </div>
</body>

</html>
