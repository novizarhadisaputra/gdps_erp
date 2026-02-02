<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Contract {{ $record->contract_number }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .signatures {
            margin-top: 50px;
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
            width: 80px;
            height: 80px;
        }

        .signature-img {
            height: 60px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="height: 60px; margin-bottom: 10px;">
        <h1>CONTRACT</h1>
        <h3>{{ $record->contract_number }}</h3>
    </div>

    <div class="section">
        <div class="section-title">Details</div>
        <table>
            <tr>
                <th>Customer</th>
                <td>{{ $record->customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Reference Proposal</th>
                <td>{{ $record->proposal->proposal_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Expiry Date</th>
                <td>{{ $record->expiry_date ? $record->expiry_date->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($record->status->value ?? $record->status) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Signatures</div>
        <div class="signatures">
            @if ($record->signatures->isNotEmpty())
                @foreach ($record->signatures as $signature)
                    @php
                        $user = $signature->user; // Relation
                        $signatureImage = $user ? $user->getFirstMediaUrl('signature') : null;
                    @endphp
                    <div class="signature-box">
                        <div style="font-weight: bold; margin-bottom: 10px;">{{ $signature->role ?? 'Signer' }}
                        </div>

                        @if ($signatureImage)
                            <img src="{{ $signatureImage }}" class="signature-img" alt="Sign">
                        @endif

                        <div style="margin: 10px 0;">
                            {{ $user->name ?? 'Unknown' }}
                        </div>

                        @if ($signature->qr_code_path)
                            <div class="qr-code">
                                <img src="data:image/svg+xml;base64,{{ base64_encode($signature->qr_code_path) }}" />
                            </div>
                        @endif

                        <div style="font-size: 10px; color: #666; margin-top: 5px;">
                            {{ $signature->signed_at->format('d M Y H:i') }}
                        </div>
                    </div>
                @endforeach
            @else
                <p>No signatures found.</p>
            @endif
        </div>
    </div>
</body>

</html>
