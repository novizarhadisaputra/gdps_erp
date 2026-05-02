@use(Carbon\Carbon)
@use(Modules\MasterData\Services\SignatureService)
@use(Spatie\MediaLibrary\MediaCollections\Models\Media)
@use(Illuminate\Support\Facades\Storage)
@use(Modules\CRM\Models\SalesOrder)
@use(Modules\CRM\Models\CooperationAgreement)
@use(Modules\CRM\Models\PurchaseOrder)
@use(Modules\CRM\Models\WorkOrder)
@use(Modules\CRM\Models\MinutesOfAgreement)
@use(Modules\CRM\Enums\SalesOrderAmendmentStatus)
@use(Modules\CRM\Enums\SalesOrderType)

@php
    $items = $record->items ?? [];
    $signatureService = app(SignatureService::class);
    $requiredApprovers = $signatureService->getRequiredApprovers($record);

    // Helper to get image as base64
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

    // Bilingual Labels & Config
    $lang = $language ?? 'id';
    $tax_percentage = $record->tax_percentage ?? 11;

    $labels = [
        'title' => [
            'id' => 'BERITA ACARA PELAKSANAAN PENYELESAIAN PEKERJAAN',
            'en' => 'WORK COMPLETION PERFORMANCE REPORT',
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
            'id' => 'telah dilaksanakan penyelesaian pemborongan pekerjaan periode',
            'en' => 'work completion has been carried out for the period of',
        ],
        'from' => [
            'id' => 'dari',
            'en' => 'from',
        ],
        'to_recipient' => [
            'id' => 'kepada',
            'en' => 'to',
        ],
        'addendum' => [
            'id' => 'Adendum',
            'en' => 'Addendum',
        ],
        'until' => [
            'id' => 'sampai dengan',
            'en' => 'until',
        ],
        'summary_intro' => [
            'id' => 'Rekapitulasi jumlah penyelesaian pekerjaan yang telah dilakukan adalah sebagai berikut:',
            'en' => 'The recapitulation of the work completion is as follows:',
        ],
        'table_no' => ['id' => 'No', 'en' => 'No'],
        'table_size' => ['id' => 'Uraian Pekerjaan', 'en' => 'Work Description'],
        'table_qty' => ['id' => 'Kuantitas', 'en' => 'Quantity'],
        'table_unit' => ['id' => 'Satuan', 'en' => 'Unit'],
        'table_price' => ['id' => 'Harga', 'en' => 'Price'],
        'table_total' => ['id' => 'Total Harga', 'en' => 'Total Price'],
        'table_so' => ['id' => 'Sales Order', 'en' => 'Sales Order'],
        'table_remarks' => ['id' => 'Keterangan', 'en' => 'Remarks'],
        'table_grand_total' => ['id' => 'Total Keseluruhan', 'en' => 'Grand Total'],
        'closing_statement' => [
            'id' => 'Demikian berita acara ini dibuat untuk dipergunakan sebagai dokumen pelengkap penagihan. Dokumen ini dapat dipertanggungjawabkan sebagaimana mestinya.',
            'en' => 'This report is made to be used as a supporting document for billing and can be accounted for accordingly.',
        ],
        'proposed_by' => ['id' => 'Yang Menyerahkan', 'en' => 'Handed Over By'],
        'received_by' => ['id' => 'Yang Menerima', 'en' => 'Received By'],
        'position' => ['id' => 'Jabatan', 'en' => 'Position'],
        'source_so' => ['id' => 'Sales Order', 'en' => 'Sales Order'],
        'source_internal' => ['id' => 'Memo Internal / ST', 'en' => 'Internal Memo / Assignment Letter'],
    ];

    $items = $record->getTranslation('items', $lang) ?? $record->items ?? [];
    $tax_wording = $record->getTranslation('tax_wording', $lang) ?? $record->tax_wording ?? ($lang === 'id' ? "Penyelesaian pekerjaan tersebut belum termasuk PPN {$tax_percentage}%" : "The above work completion does not include {$tax_percentage}% VAT");

    // Branding Assets
    $logoLogogram = imageToBase64(null, public_path('images/branding/header_left.png'));
    $logoDetail = imageToBase64(null, public_path('images/branding/header_right.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));

    $source = $record->sourceable;
    $sourceNumber = '-';
    $displaySourceType = '';
    $isInternal = false;

    // Hierarchy Implementation: PKS > PO/SPK > SO
    $displaySource = $source;
    if ($source instanceof SalesOrder && $source->sourceable) {
        // If it's an SO, check if it's based on a higher-tier document
        $parentSource = $source->sourceable;
        if ($parentSource instanceof CooperationAgreement || 
            $parentSource instanceof PurchaseOrder || 
            $parentSource instanceof WorkOrder) {
            $displaySource = $parentSource;
        }
    }

    if ($displaySource) {
        if ($displaySource instanceof CooperationAgreement) {
            $sourceNumber = $displaySource->number;
            $displaySourceType = $lang === 'id' ? 'Perjanjian Kerja Sama (PKS)' : 'Cooperation Agreement (PKS)';
        } elseif ($displaySource instanceof PurchaseOrder) {
            $sourceNumber = $displaySource->number;
            $displaySourceType = 'Purchase Order (PO)';
        } elseif ($displaySource instanceof WorkOrder) {
            $sourceNumber = $displaySource->number;
            $displaySourceType = $lang === 'id' ? 'Surat Perintah Kerja (SPK)' : 'Work Order (SPK)';
        } elseif ($displaySource instanceof SalesOrder) {
            $isInternal = $displaySource->type === SalesOrderType::Internal;
            $sourceNumber = $isInternal ? '-' : $displaySource->number;
            $displaySourceType = $isInternal ? $labels['source_internal'][$lang] : $labels['source_so'][$lang];
        } elseif ($displaySource instanceof MinutesOfAgreement) {
            $sourceNumber = $displaySource->number;
            $displaySourceType = 'Memorandum of Agreement (MoA)';
        } elseif ($displaySource instanceof \Modules\Project\Models\WorkCompletionReport) {
            $sourceNumber = $displaySource->number;
            $displaySourceType = 'BAPP';
        }
    }

    $latestAmendment = ($source instanceof SalesOrder) 
        ? $source->amendments()->where('status', SalesOrderAmendmentStatus::Approved)->latest('sequence_number')->first()
        : null;

    // Signature Data
    $pm = ($source instanceof SalesOrder) ? $source->projectManager : null;
    $pmName = $pm->name ?? '.....................';
    $pmTitle = $pm->position ?? 'Project Manager';

    // Priority: Record specific fields -> Source config -> Customer first contact -> Fallback
    $sourceConfig = ($source instanceof SalesOrder) ? $source->content_config : [];
    
    $customerContactName = $record->content_config['recipient_name'] 
        ?? $sourceConfig['recipient_name'] 
        ?? null;
    $customerContactTitle = $record->content_config['recipient_title'] 
        ?? $sourceConfig['recipient_title'] 
        ?? null;
    $customerContactGender = $record->content_config['recipient_gender'] 
        ?? $sourceConfig['recipient_gender'] 
        ?? null;

    if ((!$customerContactName || !$customerContactTitle) && !empty($record->customer?->contacts)) {
        $firstContact = $record->customer->contacts[0];
        $customerContactName = $customerContactName ?: ($firstContact['name'] ?? null);
        $customerContactTitle = $customerContactTitle ?: ($firstContact['job_position'] ?? null);
        $customerContactGender = $customerContactGender ?: ($firstContact['gender'] ?? null);
    }

    $salutation = '';
    if ($customerContactGender) {
        $salutation = ($customerContactGender === 'male' || $customerContactGender === \Modules\MasterData\Enums\Gender::Male->value) ? 'Bapak' : 'Ibu';
    }

    $customerContactName = $customerContactName ?? '.....................';
    $customerContactDisplay = $salutation ? $salutation . ' ' . $customerContactName : $customerContactName;
    $customerContactTitle = $customerContactTitle ?? '.....................';

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
                ({{ $labels['addendum'][$lang] }}: {{ $latestAmendment->number }})
            @endif,
            {{ $labels['completed_period'][$lang] }}
            <strong>{{ $record->service_period_start->translatedFormat('d F Y') }}</strong> {{ $labels['until'][$lang] }}
            <strong>{{ $record->service_period_end->translatedFormat('d F Y') }}</strong>
            {{ $labels['from'][$lang] }} PT Garuda Daya Pratama Sejahtera {{ $labels['to_recipient'][$lang] }} <strong>{{ $record->customer->name ?? '-' }}</strong>.
        </p>

        <p>{{ $labels['summary_intro'][$lang] }}</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">{{ $labels['table_no'][$lang] }}</th>
                    <th width="26%">{{ $labels['table_size'][$lang] }}</th>
                    <th width="6%">{{ $labels['table_qty'][$lang] }}</th>
                    <th width="7%">{{ $labels['table_unit'][$lang] }}</th>
                    <th width="14%">{{ $labels['table_price'][$lang] }}</th>
                    <th width="14%">{{ $labels['table_total'][$lang] }}</th>
                    <th width="14%">{{ $labels['table_so'][$lang] }}</th>
                    <th width="15%">{{ $labels['table_remarks'][$lang] }}</th>
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
                        <td>{{ $item['ukuran_pekerjaan'] ?? $item['item_name'] ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item['quantity'] ?? 0) }}</td>
                        <td class="text-center">{{ $item['uom'] ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item['so_reference'] ?? '-' }}</td>
                        <td>{{ $item['keterangan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">Subtotal</td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ number_format($record->total_amount, 0, ',', '.') }}</td>
                    <td colspan="2" style="background-color: #f2f2f2;"></td>
                </tr>
                @if($record->tax_amount > 0)
                <tr class="font-bold">
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">
                        PPN {{ (float)$record->tax_percentage }}%
                        @if($record->tax_basis === 'management_fee')
                            (DPP: Management Fee)
                        @elseif($record->tax_basis === 'custom')
                            (DPP: Manual)
                        @endif
                    </td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ number_format($record->tax_amount, 0, ',', '.') }}</td>
                    <td colspan="2" style="background-color: #f2f2f2;"></td>
                </tr>
                <tr class="font-bold">
                    <td colspan="5" class="text-right" style="background-color: #f2f2f2;">{{ $labels['table_grand_total'][$lang] }}</td>
                    <td class="text-right" style="background-color: #f2f2f2;">
                        {{ number_format($record->total_amount + $record->tax_amount, 0, ',', '.') }}</td>
                    <td colspan="2" style="background-color: #f2f2f2;"></td>
                </tr>
                @endif
            </tfoot>
        </table>

        @if($tax_wording && $tax_wording !== '-')
        <p class="font-bold" style="font-style: italic;">
            * {{ $tax_wording }}
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
