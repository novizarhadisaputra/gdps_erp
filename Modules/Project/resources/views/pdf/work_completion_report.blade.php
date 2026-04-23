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

    $latestAmendment = $record->salesOrder?->amendments()
        ?->where('status', \Modules\CRM\Enums\SalesOrderAmendmentStatus::Approved)
        ?->latest('sequence_number')
        ?->first();
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
            font-size: 10px;
            line-height: 1.4;
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .document-title {
            font-size: 14px;
            text-decoration: underline;
            margin-bottom: 2px;
        }

        .document-subtitle {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .content-section {
            margin-top: 15px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .signature-container {
            margin-top: 30px;
            width: 100%;
        }

        .signature-block {
            width: 50%;
            float: left;
            text-align: center;
        }

        .sig-space {
            height: 70px;
        }

        .doc-control {
            position: fixed;
            bottom: -1.2in;
            left: 0;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>
    <header>
        <table style="width: 100%; border: none; margin: 0;">
            <tr>
                <td style="border: none; width: 50%; text-align: left; padding: 0;">
                    @if ($logoLogogram)
                        <img src="{{ $logoLogogram }}" style="height: 160px;">
                    @endif
                </td>
                <td style="border: none; width: 50%; text-align: right; padding: 0;">
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

    <div class="doc-control">FR-UB-019 R.01</div>

    <div class="text-center">
        <div class="document-title font-bold">WORK COMPLETION REPORT</div>
        <div class="document-subtitle font-bold uppercase">PERIOD {{ $record->service_period_start->format('F Y') }}
        </div>

        <div style="margin: 10px 0;">
            <div class="font-bold">Between</div>
            <div class="uppercase font-bold">{{ $record->customer->name ?? '-' }}</div>
            <div class="font-bold">With</div>
            <div class="font-bold uppercase">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
        </div>

        <div class="font-bold">Number : {{ $record->report_number }}</div>
    </div>

    <div class="content-section">
        <p>
            Based on the Service Cooperation Agreement
            {{ $record->salesOrder->service_type ?? 'Service Provision' }},
            between <strong>{{ $record->customer->name ?? '-' }}</strong> and
            <strong>PT Garuda Daya Pratama Sejahtera</strong>
            Number: {{ $record->salesOrder->so_number ?? '-' }}
            @if($latestAmendment)
                (Amendment: {{ $latestAmendment->amendment_number }})
            @endif
            work execution has been carried out for the period
            <strong>{{ $record->service_period_start->format('d F Y') }}</strong> to
            <strong>{{ $record->service_period_end->format('d F Y') }}</strong>
            from PT Garuda Daya Pratama Sejahtera to {{ $record->customer->name ?? '-' }}.
        </p>

        <p>The summary of the work execution carried out is as follows:</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Item Description</th>
                    <th width="10%">Qty</th>
                    <th width="10%">UOM</th>
                    <th width="17%">Unit Price (IDR)</th>
                    <th width="18%">Total Price (IDR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1; $grandTotal = 0;
                @endphp
                @foreach ($items as $item)
                    @php $grandTotal += (float)($item['total_price'] ?? 0); @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>{{ $item['item_name'] ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item['quantity'] ?? 0) }}</td>
                        <td class="text-center">{{ $item['uom'] ?? '-' }}</td>
                        <td class="text-right">{{ \Illuminate\Support\Number::currency($item['unit_price'] ?? 0, 'IDR', 'id') }}</td>
                        <td class="text-right">{{ \Illuminate\Support\Number::currency($item['total_price'] ?? 0, 'IDR', 'id') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">Grand Total</td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ \Illuminate\Support\Number::currency($grandTotal, 'IDR', 'id') }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="font-bold" style="font-style: italic;">
            * The above work execution does not include 11% VAT
        </p>

        <p style="margin-top: 15px;">
            This report is prepared to be used as a supporting document for billing and can be properly accounted for.
        </p>
    </div>

    <div class="signature-container">
        <div style="margin-bottom: 40px;">
            Tangerang, {{ $record->document_date->format('d F Y') }}
        </div>

        <div class="signature-block">
            <div class="font-bold">Submitted By,</div>
            <div class="font-bold uppercase">PT Garuda Daya Pratama Sejahtera</div>
            <div class="sig-space"></div>
            <div class="font-bold">( ........................................ )</div>
            <div>Name : _____________________</div>
            <div>Position : ___________________</div>
        </div>

        <div class="signature-block">
            <div class="font-bold">Received By,</div>
            <div class="font-bold uppercase">{{ $record->customer->name ?? '-' }}</div>
            <div class="sig-space"></div>
            <div class="font-bold">( ........................................ )</div>
            <div>Name : _____________________</div>
            <div>Position : ___________________</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>

</html>
