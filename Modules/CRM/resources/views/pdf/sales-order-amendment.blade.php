@php
    $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
    $requiredApprovers = $signatureService->getRequiredApprovers($record);

    // Helper to get image as base64 - Robust Version
    if (!function_exists('imageToBase64')) {
        function imageToBase64($media, $defaultPath = null)
        {
            try {
                $content = null;
                $extension = 'png';

                if ($media && $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                    if ($media->disk === 's3') {
                        $content = \Storage::disk('s3')->get($media->getPath());
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
@use(App\Models\Role)
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales Order Amendment #{{ $record->number }} - {{ $record->salesOrder->number }}</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
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
            color: #000;
        }

        .section-header {
            background-color: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .meta-table td {
            padding: 3px 5px;
            border: 1px solid #ccc;
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

        table.data-table {
            border: 1px solid #000;
        }

        table.data-table th {
            background-color: #e5e7eb;
            border: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: center;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
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

        .delta-plus {
            color: #16a34a;
            font-weight: bold;
        }

        .delta-minus {
            color: #dc2626;
            font-weight: bold;
        }

        .signature-table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 5px;
        }

        .sig-space {
            height: 40px;
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
                <h1>SALES ORDER AMENDMENT (SOA)</h1>
                <p>No: {{ $record->number }}</p>
                <div style="font-weight: normal; font-size: 9px;">Date: {{ $record->amendment_date->format('d F Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-header">Amendment Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Original SO No.</td>
            <td class="bg-white">{{ $record->salesOrder->number }}</td>
            <td class="bg-gray">Project Code</td>
            <td class="bg-white">{{ $record->salesOrder->project->number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Customer</td>
            <td class="bg-white">{{ $record->salesOrder->customer->name ?? '-' }}</td>
            <td class="bg-gray">Amendment Status</td>
            <td class="bg-white font-bold" style="text-transform: uppercase;">{{ $record->status }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Reason for Change</td>
            <td colspan="3" class="bg-white">{{ $record->reason }}</td>
        </tr>
    </table>

    <div class="section-header">I. Detailed Amendment Comparison (Quantity & Price Changes)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="30%">Service Description / Personnel Rank</th>
                <th width="7%">Unit</th>
                <th width="10%">Qty (Old)</th>
                <th width="10%">Net Change</th>
                <th width="10%">Qty (New)</th>
                <th width="15%">New Price (IDR)</th>
                <th width="15%">Notes</th>
            </tr>
        </thead>
        <tbody>
            @php
                $beforeItems = collect($record->before_snapshot['items'] ?? []);
                $afterItems = collect($record->after_snapshot['items'] ?? []);
                $allItemNames = $beforeItems->pluck('description')->merge($afterItems->pluck('description'))->unique();
                $globalIdx = 1;
            @endphp
 
            @foreach ($allItemNames as $name)
                @php
                    $old = $beforeItems->firstWhere('description', $name);
                    $new = $afterItems->firstWhere('description', $name);
                    $oldQty = (float)($old['quantity'] ?? 0);
                    $newQty = (float)($new['quantity'] ?? 0);
                    $change = $newQty - $oldQty;
                @endphp
                <tr>
                    <td class="text-center">{{ $globalIdx++ }}</td>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ $new['uom'] ?? ($old['uom'] ?? 'Unit') }}</td>
                    <td class="text-right">{{ number_format($oldQty) }}</td>
                    <td class="text-right {{ $change > 0 ? 'delta-plus' : ($change < 0 ? 'delta-minus' : '') }}">
                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                    </td>
                    <td class="text-right">{{ number_format($newQty) }}</td>
                    <td class="text-right">{{ number_format($new['total_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="italic" style="font-size: 8px;">{{ $change != 0 ? 'Revised Qty' : '-' }}</td>
                </tr>
            @endforeach
 
            @php
                $beforeMP = collect($record->before_snapshot['manpower_details'] ?? []);
                $afterMP = collect($record->after_snapshot['manpower_details'] ?? []);
                $allMPNames = $beforeMP
                    ->pluck('job_position_name')
                    ->merge($afterMP->pluck('job_position_name'))
                    ->unique();
            @endphp
            @foreach ($allMPNames as $pos)
                @php
                    $old = $beforeMP->firstWhere('job_position_name', $pos);
                    $new = $afterMP->firstWhere('job_position_name', $pos);
                    $oldQty = (float)($old['quantity'] ?? 0);
                    $newQty = (float)($new['quantity'] ?? 0);
                    $change = $newQty - $oldQty;
                @endphp
                <tr>
                    <td class="text-center">{{ $globalIdx++ }}</td>
                    <td>{{ $pos }}</td>
                    <td class="text-center">Person</td>
                    <td class="text-right">{{ number_format($oldQty) }}</td>
                    <td class="text-right {{ $change > 0 ? 'delta-plus' : ($change < 0 ? 'delta-minus' : '') }}">
                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                    </td>
                    <td class="text-right">{{ number_format($newQty) }}</td>
                    <td class="text-right">{{ number_format($new['total_monthly_cost'] ?? ($old['total_monthly_cost'] ?? 0), 0, ',', '.') }}</td>
                    <td class="italic" style="font-size: 8px;">{{ $change != 0 ? 'Revised Headcount' : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-header">II. Amendment Summary</div>
    @php
        $sumBefore = collect($beforeItems)->sum(fn($i) => (float)($i['total_price'] ?? 0));
        $sumAfter = collect($afterItems)->sum(fn($i) => (float)($i['total_price'] ?? 0));
        $deltaAmount = (float)$sumAfter - (float)$sumBefore;

        $qtyBefore = collect($beforeMP)->sum(fn($m) => (float)($m['quantity'] ?? 0));
        $qtyAfter = collect($afterMP)->sum(fn($m) => (float)($m['quantity'] ?? 0));
        $deltaQty = (float)$qtyAfter - (float)$qtyBefore;
    @endphp
    <table class="meta-table">
        <tr class="bg-gray">
            <td width="40%">COMPONENT</td>
            <td width="20%">BEFORE</td>
            <td width="20%">AFTER</td>
            <td width="20%">DELTA</td>
        </tr>
        <tr>
            <td>Total Monthly Amount</td>
            <td class="text-right">{{ number_format($sumBefore, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($sumAfter, 0, ',', '.') }}</td>
            <td class="text-right {{ $deltaAmount >= 0 ? 'delta-plus' : 'delta-minus' }}">
                {{ $deltaAmount >= 0 ? '+' : '' }}{{ number_format($deltaAmount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Personnel</td>
            <td class="text-right">{{ number_format($qtyBefore) }} Person(s)</td>
            <td class="text-right">{{ number_format($qtyAfter) }} Person(s)</td>
            <td class="text-right {{ $deltaQty >= 0 ? 'delta-plus' : 'delta-minus' }}">
                {{ $deltaQty >= 0 ? '+' : '' }}{{ $deltaQty }} Person(s)</td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            {{-- Proposed By (Sales PIC) --}}
            <td>
                <div class="font-bold">Proposed By</div>
                <div class="sig-space"></div>
                <div class="font-bold">( {{ $record->salesOrder->salesPic->name ?? 'Account Manager' }} )</div>
                <div>PIC Sales / AMS</div>
            </td>

            {{-- Required Approvers from Rules --}}
            @foreach ($requiredApprovers as $rule)
                <td>
                    <div class="font-bold">{{ $rule->signature_type->getLabel() }}</div>
                    <div class="sig-space"></div>
                    @php
                        $eligibleUser = $signatureService->getEligibleUsers($rule)->first();
                        $displayName = $eligibleUser ? $eligibleUser->name : '..................................';
                        $displayRoles = Role::whereIn('id', $rule->approver_role ?? [])
                            ->pluck('name')
                            ->toArray();
                        $displayPositions = $rule->approver_position ?? [];
                        $combinedRole = implode(' / ', array_merge($displayRoles, $displayPositions));
                        if (empty($combinedRole)) {
                            $combinedRole = $rule->signature_type->getLabel();
                        }
                    @endphp
                    <div class="font-bold">( {{ $displayName }} )</div>
                    <div style="font-size: 8px; line-height: 1.1;">{{ $combinedRole }}</div>
                </td>
            @endforeach

            {{-- Approved By (Customer) --}}
            <td>
                <div class="font-bold">Approved By (Customer)</div>
                <div class="sig-space"></div>
                <div class="font-bold">( ........................................ )</div>
                <div>Authorized Representative</div>
            </td>
        </tr>
    </table>
</body>

</html>
