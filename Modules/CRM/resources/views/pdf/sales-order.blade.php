@use(App\Models\Role)
@use(Carbon\Carbon)
@use(Modules\CRM\Enums\SalesOrderType)
@use(Modules\MasterData\Services\SignatureService)
@use(Spatie\MediaLibrary\MediaCollections\Models\Media)
@use(Illuminate\Support\Facades\Storage)

@php
    $items = $record->content_config['items'] ?? [];
    $manpower = $record->content_config['manpower_details'] ?? [];
    $signatureService = app(SignatureService::class);
    $requiredApprovers = $signatureService->getRequiredApprovers($record);

    // Helper to get image as base64 - Robust Version
    if (!function_exists('imageToBase64')) {
        function imageToBase64($media, $defaultPath = null)
        {
            try {
                $content = null;
                $extension = 'png';

                if ($media && $media instanceof Media) {
                    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                    if ($media->disk === 's3') {
                        $content = Storage::disk('s3')->get($media->getPath());
                    } else {
                        $path = $media->getPath();
                        if (file_exists($path)) {
                            $content = file_get_contents($path);
                        }
                    }
                } elseif ($defaultPath && file_exists($defaultPath)) {
                    $extension = pathinfo($defaultPath, PATHINFO_EXTENSION);
                    $content = file_get_contents($defaultPath);
                }

                if (!$content) {
                    return null;
                }
                return 'data:image/' . $extension . ';base64,' . base64_encode($content);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    // Branding Assets
    $logoLogogram = imageToBase64(null, public_path('images/branding/header_left.png'));
    $logoDetail = imageToBase64(null, public_path('images/branding/header_right.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order - {{ $record->number }}</title>
    <style>
        @page {
            margin: 1.5in 0.7in 1.5in 0.7in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 9px;
            line-height: 1.2;
        }

        header {
            position: fixed;
            top: -1.5in;
            left: 0;
            right: -0.7in;
            z-index: 1000;
        }

        footer {
            position: fixed;
            bottom: -1.5in;
            left: -0.7in;
            right: -0.7in;
            width: 8.27in;
            line-height: 0;
            z-index: 1000;
        }

        footer .page-number:after {
            content: counter(page);
        }

        .container {
            width: 100%;
            border: 1px solid #000;
            padding: 10px;
        }

        .header-info-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        .title-box {
            text-align: right;
            vertical-align: middle;
            padding-bottom: 5px;
        }

        .title-box h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .title-box p {
            font-size: 11px;
            margin: 2px 0;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .meta-table td {
            padding: 3px 6px;
            border: 1px solid #000;
        }

        .bg-gray {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 18%;
        }

        .bg-white {
            background-color: #fff;
            width: 32%;
        }

        .section-header {
            background-color: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        table.data-table th {
            background-color: #e5e7eb;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .total-box {
            float: right;
            width: 45%;
        }

        .total-box td {
            padding: 4px 6px;
            border: 1px solid #000;
        }

        .signature-table {
            margin-top: 20px;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 8px;
        }

        .sig-space {
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px 0;
        }

        .qr-code {
            width: 60px;
            height: 60px;
        }

        .terms-table td {
            border: 1px solid #000;
            padding: 3px 6px;
        }

        .italic {
            font-style: italic;
        }
    </style>
</head>

<body>
    <header>
        <table style="width: 100%; border: none; margin: 0; padding: 0; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td style="border: none; width: 50%; padding: 0; margin: 0; text-align: left; vertical-align: top;">
                    @if ($logoLogogram)
                        <img src="{{ $logoLogogram }}"
                            style="height: 160px; width: auto; display: block; margin: 0; border: none;">
                    @endif
                </td>
                <td style="border: none; width: 50%; padding: 0; margin: 0; text-align: right; vertical-align: top;">
                    @if ($logoDetail)
                        <img src="{{ $logoDetail }}"
                            style="height: 110px; width: auto; display: block; margin: 0; margin-left: auto; border: none;">
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <footer>
        @if ($footerKop)
            <img src="{{ $footerKop }}"
                style="width: 100%; height: auto; display: block; margin: 0; padding: 0; border: none; vertical-align: bottom;">
        @endif
        <div
            style="position: absolute; bottom: 30px; right: 50px; color: #ffffff; font-size: 9px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
            Halaman <span class="page-number"></span></div>
    </footer>

    <table class="header-info-table">
        <tr>
            <td class="title-box">
                <h1>SALES ORDER / SURAT PESANAN</h1>
                <p>{{ $record->number }}</p>
                @if ($record->sourceable)
                    <div style="font-weight: normal; font-size: 10px; margin-top: 4px;">Ref:
                        {{ $record->sourceable->number }}</div>
                @endif
                <div style="font-weight: normal; font-size: 9px; margin-top: 2px;">Date:
                    {{ $record->order_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Project Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Project Name</td>
            <td class="bg-white">{{ $record->project->name ?? '-' }}</td>
            <td class="bg-gray">Project Code</td>
            <td class="bg-white">{{ $record->project->number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Customer</td>
            <td class="bg-white">{{ $record->customer->name ?? '-' }}</td>
            <td class="bg-gray">Ordering Unit</td>
            <td class="bg-white">{{ $record->project->projectArea->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Service Type</td>
            <td class="bg-white">{{ $record->service_type ?? '-' }}</td>
            <td class="bg-gray">Sales PIC</td>
            <td class="bg-white">{{ $record->salesPic->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Location</td>
            <td class="bg-white">{{ $record->job_location ?? '-' }}</td>
            <td class="bg-gray">Project Manager</td>
            <td class="bg-white">{{ $record->projectManager->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">I. Service & Personnel Details (Monthly Estimation)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Service Description / Personnel Rank</th>
                <th width="10%">Unit</th>
                <th width="10%">Qty</th>
                <th width="15%">Unit Price (IDR)</th>
                <th width="20%">Total / Month</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($items as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item['description'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['uom'] ?? 'Unit' }}</td>
                    <td class="text-right">{{ number_format($item['quantity'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
            @endforelse

            @forelse($manpower as $mp)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $mp['job_position_name'] ?? '-' }}</td>
                    <td class="text-center">Person</td>
                    <td class="text-right">{{ number_format($mp['quantity'] ?? 0) }}</td>
                    <td class="text-right">{{ number_format($mp['unit_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($mp['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
            @endforelse

            @if (empty($items) && empty($manpower))
                <tr>
                    <td colspan="6" class="text-center italic">No service or personnel details found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $taxBaseFactor = 1;
        if ($record->tax) {
            $taxBaseFactor = ($record->tax->base_rate_numerator ?? 1) / ($record->tax->base_rate_denominator ?? 1);
        }
        $taxRate = ($record->tax_percentage / 100) * $taxBaseFactor;
        $mgtFeeRate = $record->management_fee_percentage / 100;

        $subtotal = collect($items)->sum('total_price') + collect($manpower)->sum('total_price');

        // Backward compatibility: If unit_cost is missing, reverse calculate it
        // cost = price * (1 - margin)
        $totalCost =
            collect($items)->sum(
                fn($i) => ($i['unit_cost'] ?? $i['unit_price'] * (1 - $mgtFeeRate)) * ($i['quantity'] ?? 0),
            ) +
            collect($manpower)->sum(
                fn($m) => ($m['unit_cost'] ?? $m['unit_price'] * (1 - $mgtFeeRate)) * ($m['quantity'] ?? 0),
            );

        $mgtFee = $subtotal - $totalCost;
        $vat = $subtotal * $taxRate;
        $grandTotal = $subtotal + $vat;
    @endphp

    <div class="total-box">
        <table>
            <tr>
                <td class="bg-gray" style="width: 50%;">
                    {{ $record->content_config['subtotal_label'] ?? 'Subtotal (Net Cost)' }}</td>
                <td class="text-right font-bold" style="width: 50%;">
                    {{ number_format($totalCost, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="bg-gray">Management Fee ({{ $record->management_fee_percentage }}%)</td>
                <td class="text-right">
                    {{ number_format($mgtFee, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="bg-gray">{{ $record->content_config['vat_label'] ?? 'VAT' }}
                    ({{ $record->tax_percentage }}%)</td>
                <td class="text-right">
                    {{ number_format($vat, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="bg-gray" style="background-color: #000; color: #fff;">
                    {{ $record->content_config['total_label'] ?? 'Grand Total / Month' }}</td>
                <td class="text-right font-bold" style="background-color: #f3f4f6;">IDR
                    {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <div class="section-header">II. Contractual Terms</div>
    <table class="terms-table">
        <tr>
            <td class="bg-gray">Payment Terms</td>
            <td>{{ $record->payment_terms ?? 'As per company policy' }}</td>
            <td class="bg-gray">Replacement SLA</td>
            <td>{{ $record->replacement_sla ?? '3 Working Days' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Probation Period</td>
            <td>{{ $record->probation_period ?? '3 Months' }}</td>
            <td class="bg-gray">Reporting</td>
            <td>{{ $record->reporting_schedule ?? 'Last Friday of the month' }}</td>
        </tr>
    </table>

    <div class="section-header">III. Signatures & Approval</div>
    <table class="signature-table">
        <tr>
            {{-- Account Manager / Sales PIC (Owner) --}}
            @php
                $ownerSignature = $record->signatures()->where('user_id', $record->sales_pic_id)->first();
            @endphp
            <td>
                <div class="font-bold">Proposed By</div>
                <div class="sig-space" style="height: 60px;">
                    @if ($ownerSignature)
                        @php
                            $qrUrl = $signatureService->createSignatureData(
                                $ownerSignature->user,
                                $record,
                                $ownerSignature->signature_type,
                            );
                            $qrCode = $signatureService->generateQRCode($qrUrl);
                        @endphp
                        <img src="{{ $qrCode }}" class="qr-code">
                    @else
                        <div style="height: 60px; border: 1px dashed #ccc; width: 60px; margin: 0 auto;"></div>
                    @endif
                </div>
                <div class="font-bold">( {{ $record->salesPic->name ?? 'Account Manager' }} )</div>
                <div>PIC Sales / AMS</div>
            </td>

            {{-- Required Approvers from Rules --}}
            @foreach ($requiredApprovers as $rule)
                @php
                    // Find a suitable signature for this rule
                    $signature = $record->signatures()->where('signature_type', $rule->signature_type)->first();

                    // Find a suitable name to display for this role if not signed
                    $eligibleUser = $signatureService->getEligibleUsers($rule)->first();
                    $displayName = $signature
                        ? $signature->user->name
                        : ($eligibleUser
                            ? $eligibleUser->name
                            : '........................................');

                    // Build a clean display role/position
                    $displayRoles = \Spatie\Permission\Models\Role::whereIn('id', $rule->approver_role ?? [])
                        ->pluck('name')
                        ->toArray();
                    $displayPositions = $rule->approver_position ?? [];
                    $combinedRole = implode(' / ', array_merge($displayRoles, $displayPositions));

                    if (empty($combinedRole)) {
                        $combinedRole = $rule->signature_type->getLabel();
                    }
                @endphp
                <td>
                    <div class="font-bold">{{ $rule->signature_type->getLabel() }}</div>
                    <div class="sig-space" style="height: 60px;">
                        @if ($signature)
                            @php
                                $qrUrl = $signatureService->createSignatureData(
                                    $signature->user,
                                    $record,
                                    $signature->signature_type,
                                );
                                $qrCode = $signatureService->generateQRCode($qrUrl);
                            @endphp
                            <img src="{{ $qrCode }}" class="qr-code">
                        @else
                            <div style="height: 60px; border: 1px dashed #ccc; width: 60px; margin: 0 auto;"></div>
                        @endif
                    </div>
                    <div class="font-bold">( {{ $displayName }} )</div>
                    <div style="font-size: 8px; line-height: 1.1;">{{ $combinedRole }}</div>
                </td>
            @endforeach

            {{-- Client Signature Placeholder (Manual) - Only for External --}}
            @if ($record->type === SalesOrderType::External)
                <td>
                    <div class="font-bold">Approved By (Customer)</div>
                    <div class="sig-space" style="height: 40px;"></div>
                    <div class="font-bold">( ........................................ )</div>
                    <div>Authorized Representative</div>
                </td>
            @endif
        </tr>
    </table>
</body>

</html>
