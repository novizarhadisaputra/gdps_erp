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

    // Signature Data
    $pm = $record->salesOrder?->projectManager;
    $pmName = $pm->name ?? '.....................';
    $pmTitle = $pm->position ?? 'Project Manager';

    // Priority: Record specific fields -> SO config -> Customer first contact -> Fallback
    $customerContactName = $record->content_config['recipient_name'] 
        ?? $record->salesOrder?->content_config['recipient_name'] 
        ?? null;
    $customerContactTitle = $record->content_config['recipient_title'] 
        ?? $record->salesOrder?->content_config['recipient_title'] 
        ?? null;
    $customerContactGender = $record->content_config['recipient_gender'] 
        ?? $record->salesOrder?->content_config['recipient_gender'] 
        ?? null;

    if (!$customerContactName && !empty($record->customer?->contacts)) {
        $firstContact = $record->customer->contacts[0];
        $customerContactName = $firstContact['name'] ?? null;
        $customerContactTitle = $firstContact['job_position'] ?? null;
        $customerContactGender = $firstContact['gender'] ?? null;
    }

    $salutation = '';
    if ($customerContactGender) {
        $salutation = ($customerContactGender === 'male' || $customerContactGender === \Modules\MasterData\Enums\Gender::Male->value) ? 'Bapak' : 'Ibu';
    }

    $customerContactName = $customerContactName ?? '.....................';
    $customerContactDisplay = $salutation ? $salutation . ' ' . $customerContactName : $customerContactName;
    $customerContactTitle = $customerContactTitle ?? 'Jabatan';
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

        .title-box div {
            font-size: 9px;
            font-weight: normal;
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
        <div style="position: absolute; bottom: 30px; right: 50px; color: #ffffff; font-size: 9px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
            Halaman <span class="page-number"></span></div>
    </footer>

    <div class="doc-control">FR-UB-019 R.01</div>

    <table class="header-info-table">
        <tr>
            <td class="title-box">
                <h1>BERITA ACARA PENYELESAIAN PEKERJAAN</h1>
                <p>Nomor : {{ $record->report_number }}</p>
                <div>Periode : {{ $record->service_period_start->format('F Y') }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; text-align: center;">
        <div style="margin: 10px 0;">
            <div class="font-bold">Antara</div>
            <div class="uppercase font-bold">{{ $record->customer->name ?? '-' }}</div>
            <div class="font-bold">Dengan</div>
            <div class="font-bold uppercase">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
        </div>
    </div>

    <div class="content-section">
        <p>
            Berdasarkan Perjanjian Kerja Sama Jasa
            {{ $record->salesOrder->service_type ?? 'Penyediaan Jasa' }},
            antara <strong>{{ $record->customer->name ?? '-' }}</strong> dan
            <strong>PT Garuda Daya Pratama Sejahtera</strong>
            Nomor: {{ $record->salesOrder->so_number ?? '-' }}
            @if($latestAmendment)
                (Adendum: {{ $latestAmendment->amendment_number }})
            @endif
            telah dilaksanakan pekerjaan untuk periode
            <strong>{{ $record->service_period_start->format('d F Y') }}</strong> sampai dengan
            <strong>{{ $record->service_period_end->format('d F Y') }}</strong>
            dari PT Garuda Daya Pratama Sejahtera kepada {{ $record->customer->name ?? '-' }}.
        </p>

        <p>Ringkasan pelaksanaan pekerjaan yang telah dilaksanakan adalah sebagai berikut:</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Deskripsi Item</th>
                    <th width="10%">Jml</th>
                    <th width="10%">Satuan</th>
                    <th width="17%">Harga Satuan (IDR)</th>
                    <th width="18%">Total Harga (IDR)</th>
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
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">Total Keseluruhan</td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ \Illuminate\Support\Number::currency($grandTotal, 'IDR', 'id') }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="font-bold" style="font-style: italic;">
            * Pelaksanaan pekerjaan di atas belum termasuk PPN 11%
        </p>

        <p style="margin-top: 15px;">
            Demikian laporan ini dibuat untuk dipergunakan sebagai dokumen pendukung penagihan dan dapat dipertanggungjawabkan sebagaimana mestinya.
        </p>
    </div>

    <div class="signature-container">
        <div style="margin-bottom: 40px;">
            Tangerang, {{ $record->document_date->format('d F Y') }}
        </div>

        <div class="signature-block">
            <div class="font-bold">Diajukan Oleh,</div>
            <div class="font-bold uppercase">PT Garuda Daya Pratama Sejahtera</div>
            <div class="sig-space"></div>
            <div class="font-bold">( {{ $pmName }} )</div>
            <div>Jabatan : {{ $pmTitle }}</div>
        </div>

        <div class="signature-block">
            <div class="font-bold">Diterima Oleh,</div>
            <div class="font-bold uppercase">{{ $record->customer->name ?? '-' }}</div>
            <div class="sig-space"></div>
            <div class="font-bold">( {{ $customerContactDisplay }} )</div>
            <div>Jabatan : {{ $customerContactTitle }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>

</html>
