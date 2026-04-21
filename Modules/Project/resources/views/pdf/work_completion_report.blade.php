@php
    $items = $record->items ?? [];
    $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
    $requiredApprovers = $signatureService->getRequiredApprovers($record);

    // Helper to get image as base64
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

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>BAPP - {{ $record->report_number }}</title>
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
            z-index: 1000;
        }

        .header-info-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
        }

        .title-box {
            text-align: right;
            padding-bottom: 5px;
        }

        .title-box h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
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

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .signature-table {
            margin-top: 20px;
            width: 100%;
            table-layout: fixed;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 8px;
        }

        .sig-space {
            min-height: 50px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <header>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: left;">
                    @if ($logoLogogram)
                        <img src="{{ $logoLogogram }}" style="height: 160px;">
                    @endif
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    @if ($logoDetail)
                        <img src="{{ $logoDetail }}" style="height: 110px;">
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <footer>
        @if ($footerKop)
            <img src="{{ $footerKop }}" style="width: 100%;">
        @endif
    </footer>

    <table class="header-info-table">
        <tr>
            <td class="title-box">
                <h1>BERITA ACARA PEMERIKSAAN PEKERJAAN (BAPP)</h1>
                <p>No: {{ $record->report_number }}</p>
                <div style="font-weight: normal; font-size: 9px;">Date: {{ $record->document_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Project Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Project Name</td>
            <td class="bg-white">{{ $record->project->name ?? '-' }}</td>
            <td class="bg-gray">Project Code</td>
            <td class="bg-white">{{ $record->project->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Customer</td>
            <td class="bg-white">{{ $record->customer->name ?? '-' }}</td>
            <td class="bg-gray">Sales Order Ref</td>
            <td class="bg-white">{{ $record->salesOrder->so_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-gray">Service Period</td>
            <td class="bg-white" colspan="3">
                {{ $record->service_period_start->format('d/m/Y') }} - {{ $record->service_period_end->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <div class="section-header">Work Completion Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Description of Work</th>
                <th width="10%">Qty / Vol</th>
                <th width="10%">Unit</th>
                <th width="15%">Price/Unit (IDR)</th>
                <th width="15%">Total (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $grandTotal = 0; @endphp
            @forelse($items as $item)
                @php $grandTotal += ($item['total_price'] ?? 0); @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item['item_name'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item['quantity'] ?? 0) }}</td>
                    <td class="text-center">{{ $item['uom'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No work details recorded.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="5" class="bg-gray text-right">Grand Total Work Value</td>
                <td class="text-right">IDR {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($record->description)
        <div class="section-header">Description / Remarks</div>
        <div style="border: 1px solid #000; padding: 10px; min-height: 40px;">
            {{ $record->description }}
        </div>
    @endif

    <div class="section-header">Signatures & Verification</div>
    <table class="signature-table">
        <tr>
            <td>
                <div class="font-bold">Prepared By</div>
                <div class="sig-space"></div>
                <div class="font-bold">( ........................................ )</div>
                <div>Project Admin / Analyst</div>
            </td>
            @foreach ($requiredApprovers as $rule)
                <td>
                    <div class="font-bold">{{ $rule->signature_type->getLabel() }}</div>
                    <div class="sig-space"></div>
                    <div class="font-bold">( ........................................ )</div>
                </td>
            @endforeach
            <td>
                <div class="font-bold">Verified By (Customer)</div>
                <div class="sig-space"></div>
                <div class="font-bold">( ........................................ )</div>
                <div>Authorized Client Rep.</div>
            </td>
        </tr>
    </table>
</body>
</html>
