<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Contract - {{ $record->contract_number }}</title>
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
            color: #059669;
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
            font-size: 12px;
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

        .qr-code img {
            width: 70px;
            height: 70px;
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
                <h1>CONTRACT</h1>
                <p>{{ $record->contract_number }}</p>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Date: {{ now()->format('d M Y') }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="content">
            <div class="section-title">Contract Information</div>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td class="info-value">{{ $record->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Contract Number</th>
                    <td class="info-value">{{ $record->contract_number }}</td>
                </tr>
                <tr>
                    <th>Reference Proposal</th>
                    <td>{{ $record->proposal->proposal_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td class="info-value">{{ $record->expiry_date ? $record->expiry_date->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Workflow Status</th>
                    <td>
                        <span
                            style="background-color: #ecfdf5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase;">
                            {{ $record->status->value ?? $record->status }}
                        </span>
                    </td>
                </tr>
            </table>

            <!-- Signatures -->
            <div class="section-title">Digital Approvals</div>
            <table class="signatures" style="border: none;">
                <tr style="border: none;">
                    @if ($record->signatures && $record->signatures->isNotEmpty())
                        @foreach ($record->signatures as $signature)
                            @php
                                $user = $signature->user;
                            @endphp
                            <td class="signature-box" style="border: none;">
                                <div class="signature-role">{{ $signature->role ?? 'Signer' }}</div>

                                @if ($user)
                                    @php
                                        $service = app(\Modules\MasterData\Services\SignatureService::class);
                                        $qrUrl = $service->createSignatureData(
                                            $user,
                                            $record,
                                            $signature->signature_type ?? 'approved',
                                        );
                                        $qrCodeDataUri = $service->generateQRCode($qrUrl);
                                    @endphp
                                    <div class="qr-code">
                                        <img src="{{ $qrCodeDataUri }}" />
                                    </div>
                                @else
                                    <div style="height: 70px;"></div>
                                @endif

                                <div class="signature-name">
                                    {{ $user->name ?? 'Unknown' }}
                                </div>
                                <div class="signed-date">
                                    {{ $signature->signed_at->format('d M Y H:i') }}
                                </div>
                            </td>
                        @endforeach
                        <!-- Pad empty cells if less than 3 -->
                        @for ($i = $record->signatures->count(); $i < 3; $i++)
                            <td class="signature-box" style="border: none;"></td>
                        @endfor
                    @else
                        <td colspan="3" style="text-align: center; color: #94a3b8; border: none; padding: 40px;">No
                            signatures found.</td>
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
