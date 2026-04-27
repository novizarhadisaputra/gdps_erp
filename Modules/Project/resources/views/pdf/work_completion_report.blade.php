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

    $source = $record->sourceable;
    $sourceNumber = '-';
    $displaySourceType = '';
    $isInternal = false;

    if ($source) {
        if ($source instanceof \Modules\CRM\Models\SalesOrder) {
            $isInternal = $source->type === \Modules\CRM\Enums\SalesOrderType::Internal;
            $sourceNumber = $isInternal ? '-' : $source->number;
            $displaySourceType = $isInternal ? $labels['source_internal'][$lang] : $labels['source_so'][$lang];
        } elseif ($source instanceof \Modules\CRM\Models\PurchaseOrder) {
            $sourceNumber = $source->number;
            $displaySourceType = 'Purchase Order (PO)';
        } elseif ($source instanceof \Modules\CRM\Models\WorkOrder) {
            $sourceNumber = $source->number;
            $displaySourceType = 'Surat Perintah Kerja (SPK)';
        } elseif ($source instanceof \Modules\CRM\Models\CooperationAgreement) {
            $sourceNumber = $source->number;
            $displaySourceType = 'Perjanjian Kerja Sama (PKS)';
        } elseif ($source instanceof \Modules\CRM\Models\MinutesOfAgreement) {
            $sourceNumber = $source->number;
            $displaySourceType = 'Memorandum of Agreement (MoA)';
        } elseif ($source instanceof \Modules\Project\Models\WorkCompletionReport) {
            $sourceNumber = $source->number;
            $displaySourceType = 'BAPP';
        }
    }

    $latestAmendment = ($source instanceof \Modules\CRM\Models\SalesOrder) 
        ? $source->amendments()->where('status', \Modules\CRM\Enums\SalesOrderAmendmentStatus::Approved)->latest('sequence_number')->first()
        : null;

    // Signature Data
    $pm = ($source instanceof \Modules\CRM\Models\SalesOrder) ? $source->projectManager : null;
    $pmName = $pm->name ?? '.....................';
    $pmTitle = $pm->position ?? 'Project Manager';

    // Priority: Record specific fields -> Source config -> Customer first contact -> Fallback
    $sourceConfig = ($source instanceof \Modules\CRM\Models\SalesOrder) ? $source->content_config : [];
    
    $customerContactName = $record->content_config['recipient_name'] 
        ?? $sourceConfig['recipient_name'] 
        ?? null;
    $customerContactTitle = $record->content_config['recipient_title'] 
        ?? $sourceConfig['recipient_title'] 
        ?? null;
    $customerContactGender = $record->content_config['recipient_gender'] 
        ?? $sourceConfig['recipient_gender'] 
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

    // Bilingual Labels
    $lang = $language ?? 'id';
    $items = $record->getTranslation('items', $lang) ?? $record->items ?? [];
    $tax_wording = $record->getTranslation('tax_wording', $lang) ?? $record->tax_wording ?? ($lang === 'id' ? 'Penyelesaian pekerjaan di atas belum termasuk PPN 11%' : 'The above work completion does not include 11% VAT');

    $labels = [
        'title' => [
            'id' => 'BERITA ACARA PENYELESAIAN PEKERJAAN',
            'en' => 'WORK COMPLETION REPORT',
        ],
        'number' => [
            'id' => 'Nomor',
            'en' => 'Number',
        ],
        'period' => [
            'id' => 'Periode',
            'en' => 'Period',
        ],
        'page' => [
            'id' => 'Halaman',
            'en' => 'Page',
        ],
        'between' => [
            'id' => 'Antara',
            'en' => 'Between',
        ],
        'with' => [
            'id' => 'Dengan',
            'en' => 'And',
        ],
        'based_on' => [
            'id' => 'Berdasarkan',
            'en' => 'Based on',
        ],
        'completed_period' => [
            'id' => 'telah diselesaikan pekerjaan untuk periode',
            'en' => 'the work has been completed for the period',
        ],
        'until' => [
            'id' => 'sampai dengan',
            'en' => 'until',
        ],
        'summary_intro' => [
            'id' => 'Ringkasan penyelesaian pekerjaan yang telah diselesaikan adalah sebagai berikut:',
            'en' => 'The summary of work completion is as follows:',
        ],
        'table_no' => ['id' => 'No', 'en' => 'No'],
        'table_desc' => ['id' => 'Deskripsi Item', 'en' => 'Item Description'],
        'table_qty' => ['id' => 'Jml', 'en' => 'Qty'],
        'table_unit' => ['id' => 'Satuan', 'en' => 'Unit'],
        'table_price' => ['id' => 'Harga Satuan', 'en' => 'Unit Price'],
        'table_total' => ['id' => 'Total Harga', 'en' => 'Total Price'],
        'table_grand_total' => ['id' => 'Total Keseluruhan', 'en' => 'Grand Total'],
        'closing_statement' => [
            'id' => 'Demikian laporan ini dibuat untuk dipergunakan sebagai dokumen pendukung penagihan dan dapat dipertanggungjawabkan sebagaimana mestinya.',
            'en' => 'This report is made to be used as a supporting document for billing and can be accounted for accordingly.',
        ],
        'proposed_by' => ['id' => 'Diajukan Oleh', 'en' => 'Proposed By'],
        'received_by' => ['id' => 'Diterima Oleh', 'en' => 'Received By'],
        'position' => ['id' => 'Jabatan', 'en' => 'Position'],
        'source_so' => ['id' => 'Sales Order', 'en' => 'Sales Order'],
        'source_internal' => ['id' => 'Memo Internal / ST', 'en' => 'Internal Memo / Assignment Letter'],
    ];

    $soType = $record->salesOrder?->type;
    $isInternal = $soType === \Modules\CRM\Enums\SalesOrderType::Internal;
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>BAPP - {{ $record->number }}</title>
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
            text-align: center;
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
            {{ $labels['page'][$lang] }} <span class="page-number"></span></div>
    </footer>

    <div class="doc-control">FR-UB-019 R.01</div>

    <table class="header-info-table">
        <tr>
            <td class="title-box">
                <h1>{{ $labels['title'][$lang] }}</h1>
                <p>{{ $labels['number'][$lang] }} : {{ $record->number }}</p>
                <div>{{ $labels['period'][$lang] }} : {{ $record->service_period_start->translatedFormat('F Y') }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; text-align: center;">
        <div style="margin: 10px 0;">
            <div class="font-bold">{{ $labels['between'][$lang] }}</div>
            <div class="uppercase font-bold">{{ $record->customer->name ?? '-' }}</div>
            <div class="font-bold">{{ $labels['with'][$lang] }}</div>
            <div class="font-bold uppercase">PT GARUDA DAYA PRATAMA SEJAHTERA</div>
        </div>
    </div>

    <div class="content-section">
        <p>
            {{ $labels['based_on'][$lang] }} <strong>{{ $displaySourceType }}</strong>
            {{ $labels['number'][$lang] }}: <strong>{{ $sourceNumber }}</strong>
            @if($latestAmendment)
                (Adendum: {{ $latestAmendment->number }})
            @endif
            {{ $labels['completed_period'][$lang] }}
            <strong>{{ $record->service_period_start->translatedFormat('d F Y') }}</strong> {{ $labels['until'][$lang] }}
            <strong>{{ $record->service_period_end->translatedFormat('d F Y') }}</strong>
            {{ $lang === 'id' ? 'dari' : 'from' }} PT Garuda Daya Pratama Sejahtera {{ $lang === 'id' ? 'kepada' : 'to' }} {{ $record->customer->name ?? '-' }}.
        </p>

        <p>{{ $labels['summary_intro'][$lang] }}</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">{{ $labels['table_no'][$lang] }}</th>
                    <th width="40%">{{ $labels['table_desc'][$lang] }}</th>
                    <th width="10%">{{ $labels['table_qty'][$lang] }}</th>
                    <th width="10%">{{ $labels['table_unit'][$lang] }}</th>
                    <th width="17%">{{ $labels['table_price'][$lang] }} (IDR)</th>
                    <th width="18%">{{ $labels['table_total'][$lang] }} (IDR)</th>
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
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">{{ $labels['table_grand_total'][$lang] }}</td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ \Illuminate\Support\Number::currency($grandTotal, 'IDR', 'id') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($tax_wording && $tax_wording !== '-')
        <p class="font-bold" style="font-style: italic;">
            * {{ $tax_wording }}
        </p>
        @elseif($isInternal)
        <p class="font-bold" style="font-style: italic;">
            * -
        </p>
        @endif

        <p style="margin-top: 15px;">
            {{ $labels['closing_statement'][$lang] }}
        </p>
    </div>

    <div class="signature-container">
        <div style="margin-bottom: 40px;">
            Tangerang, {{ $record->document_date->translatedFormat('d F Y') }}
        </div>

        <div class="signature-block">
            <div class="font-bold">{{ $labels['proposed_by'][$lang] }},</div>
            <div class="font-bold uppercase">PT Garuda Daya Pratama Sejahtera</div>
            <div class="sig-space"></div>
            <div class="font-bold">( {{ $pmName }} )</div>
            <div>{{ $labels['position'][$lang] }} : {{ $pmTitle }}</div>
        </div>

        <div class="signature-block">
            <div class="font-bold">{{ $labels['received_by'][$lang] }},</div>
            <div class="font-bold uppercase">{{ $record->customer->name ?? '-' }}</div>
            <div class="sig-space"></div>
            <div class="font-bold">( {{ $customerContactDisplay }} )</div>
            <div>{{ $labels['position'][$lang] }} : {{ $customerContactTitle }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>

</html>
