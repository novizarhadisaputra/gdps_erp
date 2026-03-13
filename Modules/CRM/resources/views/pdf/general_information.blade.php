<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>General Information - {{ $record->customer->name ?? 'Project' }}</title>
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
            background-color: #fafafa;
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
            font-size: 22px;
            margin: 0;
            color: #0f172a;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        .document-type p {
            font-size: 11px;
            color: #64748b;
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
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            width: 30%;
        }

        td {
            padding: 8px 12px;
            border: 1px solid #f1f5f9;
            vertical-align: top;
            color: #334155;
        }

        .info-value {
            font-weight: 500;
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
            margin-bottom: 10px;
        }

        .signature-img {
            height: 45px;
            margin: 5px 0;
        }

        .signature-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 10px;
        }

        .qr-code img {
            width: 60px;
            height: 60px;
            margin-top: 5px;
        }

        .signed-date {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 4px;
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

        ul {
            margin: 0;
            padding-left: 15px;
        }

        li {
            margin-bottom: 3px;
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
                <h1>GENERAL INFORMATION</h1>
                <p>{{ $record->document_number ?? '-' }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ now()->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <!-- Project Information -->
            <div class="section-title">Project Information</div>
            <table>
                <tr>
                    <th>Customer</th>
                    <td class="info-value">{{ $record->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Project Scope (SOW)</th>
                    <td>{{ $record->scope_of_work ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $record->location ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Estimation Dates</th>
                    <td>
                        <strong>Start:</strong>
                        {{ $record->estimated_start_date ? $record->estimated_start_date->format('d M Y') : '-' }}
                        &nbsp;&bull;&nbsp;
                        <strong>End:</strong>
                        {{ $record->estimated_end_date ? $record->estimated_end_date->format('d M Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{!! nl2br(e($record->description ?? '-')) !!}</td>
                </tr>
                <tr>
                    <th>Work Activities</th>
                    <td>{!! nl2br(e($record->work_activities ?? '-')) !!}</td>
                </tr>
            </table>

            <!-- Requirements & Qualifications -->
            <div class="section-title">Requirements & Qualifications</div>
            <table>
                <tr>
                    <th>Manpower Qualifications</th>
                    <td>{!! nl2br(e($record->manpower_qualifications ?? '-')) !!}</td>
                </tr>
                <tr>
                    <th>Service Level</th>
                    <td>{!! nl2br(e($record->service_level ?? '-')) !!}</td>
                </tr>
                <tr>
                    <th>Billing Requirements</th>
                    <td>{!! nl2br(e($record->billing_requirements ?? '-')) !!}</td>
                </tr>
            </table>

            <!-- Risk & PIC -->
            <div class="section-title">Risk Assessment & PIC</div>
            <table>
                <tr>
                    <th>Risk Register No.</th>
                    <td class="info-value">{{ $record->risk_register_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Risk Management</th>
                    <td>
                        @if (is_array($record->risk_management) && count($record->risk_management) > 0)
                            <ul>
                                @foreach ($record->risk_management as $risk)
                                    <li>{{ is_array($risk) ? implode(', ', $risk) : $risk }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ $record->risk_management ? (is_string($record->risk_management) ? $record->risk_management : '-') : '-' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Customer PIC</th>
                    <td>
                        <span class="info-value">{{ $record->pic_customer_name }}</span>
                        @if ($record->pic_customer_phone)
                            <div style="font-size: 10px; color: #64748b;">Phone: {{ $record->pic_customer_phone }}
                            </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td>{!! nl2br(e($record->remarks ?? '-')) !!}</td>
                </tr>
            </table>

            <!-- Signatures -->
            <div class="section-title">Digital Approvals</div>
            <table class="signatures" style="border: none;">
                <tr style="border: none;">
                    @if ($record->signatures && count($record->signatures) > 0)
                        @foreach ($record->signatures as $signature)
                            @php
                                $signature = (object) $signature;
                                $user = \App\Models\User::find($signature->user_id ?? null);
                                $signatureImage = $user ? $user->getFirstMediaUrl('signature') : null;
                            @endphp
                            <td class="signature-box" style="border: none;">
                                <div class="signature-role">{{ $signature->role ?? 'Signer' }}</div>

                                @if ($user)
                                    @php
                                        $service = app(\Modules\MasterData\Services\SignatureService::class);
                                        $qrUrl = $service->createSignatureData(
                                            $user,
                                            $record,
                                            $signature->signature_type ?? \Modules\MasterData\Enums\ApprovalSignatureType::Approver->value,
                                        );
                                        $qrCodeDataUri = $service->generateQRCode($qrUrl);
                                    @endphp
                                    <div class="qr-code">
                                        <img src="{{ $qrCodeDataUri }}" />
                                    </div>
                                @else
                                    <div style="height: 60px;"></div>
                                @endif

                                <div class="signature-name">
                                    {{ $user->name ?? ($signature->user_name ?? 'Unknown') }}
                                </div>
                                <div class="signed-date">
                                    {{ isset($signature->signed_at) ? \Carbon\Carbon::parse($signature->signed_at)->format('d M Y H:i') : '-' }}
                                </div>
                            </td>
                        @endforeach
                        <!-- Pad empty cells if less than 3 -->
                        @for ($i = count($record->signatures); $i < 3; $i++)
                            <td class="signature-box" style="border: none;"></td>
                        @endfor
                    @else
                        <td colspan="3" style="text-align: center; color: #94a3b8; border: none; padding: 40px;">No
                            signatures available.</td>
                    @endif
                </tr>
            </table>
        </div>

        <div class="footer">
            PT Garuda Daya Pratama Sejahtera &nbsp;&bull;&nbsp; Digital Document Verification System
        </div>
    </div>
</body>

</html>
