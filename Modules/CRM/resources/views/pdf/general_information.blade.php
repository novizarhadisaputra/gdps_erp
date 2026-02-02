<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>General Information - {{ $record->customer->name ?? 'Project' }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            font-size: 11pt;
            border-bottom: 1px solid #ccc;
            margin-bottom: 8px;
            padding-bottom: 2px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f9f9f9;
            width: 30%;
            font-weight: bold;
        }

        .signatures {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            border: 1px solid #eee;
            padding: 10px;
            vertical-align: top;
        }

        .qr-code img {
            width: 70px;
            height: 70px;
        }

        .signature-img {
            height: 50px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="height: 60px; margin-bottom: 10px;">
        <h1 style="margin-bottom: 5px;">GENERAL INFORMATION</h1>
        <h3 style="margin: 0;">{{ $record->document_number ?? $record->customer->name }}</h3>
        <p style="margin-top: 5px; color: #666;">Date: {{ now()->format('d M Y') }}</p>
    </div>

    <!-- Project Information -->
    <div class="section">
        <div class="section-title">Project Information</div>
        <table>
            <tr>
                <th>Document Number</th>
                <td>{{ $record->document_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Customer</th>
                <td>{{ $record->customer->name ?? '-' }}</td>
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
                    <b>Start:</b>
                    {{ $record->estimated_start_date ? $record->estimated_start_date->format('d M Y') : '-' }} <br>
                    <b>End:</b> {{ $record->estimated_end_date ? $record->estimated_end_date->format('d M Y') : '-' }}
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
    </div>

    <!-- Requirements & Qualifications -->
    <div class="section">
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
    </div>

    <!-- Risk & PIC -->
    <div class="section">
        <div class="section-title">Risk Assessment & PIC</div>
        <table>
            <tr>
                <th>Risk Register No.</th>
                <td>{{ $record->risk_register_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Risk Management</th>
                <td>
                    @if (is_array($record->risk_management) && count($record->risk_management) > 0)
                        <ul style="margin: 0; padding-left: 20px;">
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
                <td>{{ $record->pic_customer_name }} @if ($record->pic_customer_phone)
                        ({{ $record->pic_customer_phone }})
                    @endif
                </td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{!! nl2br(e($record->remarks ?? '-')) !!}</td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <div class="section">
        <div class="section-title">Approvals</div>
        <div class="signatures">
            @if ($record->signatures && count($record->signatures) > 0)
                @foreach ($record->signatures as $signature)
                    @php
                        // Handle signature as array struct because it's json casted
$signature = (object) $signature;
$user = \App\Models\User::find($signature->user_id ?? null);
// We need to fetch media from user model if available
$signatureImage = $user ? $user->getFirstMediaUrl('signature') : null;
                    @endphp
                    <div class="signature-box">
                        <div style="font-weight: bold; margin-bottom: 10px;">{{ $signature->role ?? 'Signer' }}</div>

                        @if ($signatureImage)
                            <img src="{{ $signatureImage }}" class="signature-img" alt="Sign">
                        @else
                            <div style="height: 50px;"></div>
                        @endif

                        <div style="margin: 10px 0; font-weight: bold;">
                            {{ $user->name ?? ($signature->user_name ?? 'Unknown') }}
                        </div>

                        @if (isset($signature->qr_code_path) && $signature->qr_code_path)
                            <div class="qr-code">
                                <img src="data:image/svg+xml;base64,{{ base64_encode($signature->qr_code_path) }}" />
                            </div>
                        @endif

                        <div style="font-size: 10px; color: #666; margin-top: 5px;">
                            {{ isset($signature->signed_at) ? \Carbon\Carbon::parse($signature->signed_at)->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                @endforeach
            @else
                <p style="text-align: center; color: #999;">No signatures available.</p>
            @endif
        </div>
    </div>
</body>

</html>
