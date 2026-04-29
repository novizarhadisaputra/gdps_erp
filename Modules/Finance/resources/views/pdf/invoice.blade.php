@php
    if (!function_exists('terbilang')) {
        function terbilang($x)
        {
            $angka = [
                '',
                'Satu',
                'Dua',
                'Tiga',
                'Empat',
                'Lima',
                'Enam',
                'Tujuh',
                'Delapan',
                'Sembilan',
                'Sepuluh',
                'Sebelas',
            ];
            if ($x < 12) {
                return ' ' . $angka[$x];
            } elseif ($x < 20) {
                return terbilang($x - 10) . ' Belas';
            } elseif ($x < 100) {
                return terbilang($x / 10) . ' Puluh' . terbilang($x % 10);
            } elseif ($x < 200) {
                return 'Seratus' . terbilang($x - 100);
            } elseif ($x < 1000) {
                return terbilang($x / 100) . ' Ratus' . terbilang($x % 100);
            } elseif ($x < 2000) {
                return 'Seribu' . terbilang($x - 1000);
            } elseif ($x < 1000000) {
                return terbilang($x / 1000) . ' Ribu' . terbilang($x % 1000);
            } elseif ($x < 1000000000) {
                return terbilang($x / 1000000) . ' Juta' . terbilang($x % 1000000);
            } elseif ($x < 1000000000000) {
                return terbilang($x / 1000000000) . ' Milyar' . terbilang(fmod($x, 1000000000));
            } elseif ($x < 1000000000000000) {
                return terbilang($x / 1000000000000) . ' Trilyun' . terbilang(fmod($x, 1000000000000));
            }
            return '';
        }
    }

    if (!function_exists('imageToBase64')) {
        function imageToBase64($media, $defaultPath = null)
        {
            try {
                $content = null;
                $extension = 'png';
                if ($defaultPath && file_exists($defaultPath)) {
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

    $lang = $language ?? 'id';

    // Bilingual Labels
    $labels = [
        'invoice_no' => ['id' => 'No Invoice', 'en' => 'Invoice No'],
        'top' => ['id' => 'Term of Payment', 'en' => 'Term of Payment'],
        'source_doc' => ['id' => 'No PKS / No. PO', 'en' => 'PKS No / PO No'],
        'invoice_date' => ['id' => 'Tanggal Invoice', 'en' => 'Invoice Date'],
        'ref_no' => ['id' => 'No Ref', 'en' => 'Ref No'],
        'to' => ['id' => 'Kepada yang terhormat', 'en' => 'To the honorable'],
        'accounting_unit' => ['id' => 'Unit Accounting', 'en' => 'Accounting Unit'],
        'table_no' => ['id' => 'No', 'en' => 'No'],
        'table_desc' => ['id' => 'Keterangan', 'en' => 'Description'],
        'table_qty' => ['id' => 'Qty', 'en' => 'Qty'],
        'table_unit' => ['id' => 'Satuan', 'en' => 'Unit'],
        'table_price' => ['id' => 'Harga Satuan', 'en' => 'Unit Price'],
        'table_total' => ['id' => 'Total Harga', 'en' => 'Total Price'],
        'terbilang' => ['id' => 'Terbilang', 'en' => 'Spelled Out'],
        'sub_total' => ['id' => 'Sub Total', 'en' => 'Sub Total'],
        'tax_base' => ['id' => 'DPP Nilai Lain', 'en' => 'Taxable Base'],
        'total' => ['id' => 'Total', 'en' => 'Total'],
        'payment_instruction' => [
            'id' => 'Pembayaran dapat dilakukan melalui transfer ke rekening kami :',
            'en' => 'Payment can be made via transfer to our account :',
        ],
        'an' => ['id' => 'a.n.', 'en' => 'a.n.'], // Keep a.n. as requested by user for branding
        'signed_by' => ['id' => 'Digitally Signed By', 'en' => 'Digitally Signed By'],
        'page' => ['id' => 'Halaman', 'en' => 'Page'],
        'source_so' => ['id' => 'No Sales Order', 'en' => 'Sales Order No'],
        'source_internal' => ['id' => 'No Memo Internal / ST', 'en' => 'Internal Memo / ST No'],
        'days' => ['id' => 'Hari', 'en' => 'Days'],
        'pks' => ['id' => 'Perjanjian Kerja Sama (PKS)', 'en' => 'Cooperation Agreement (PKS)'],
        'spk' => ['id' => 'Surat Perintah Kerja (SPK)', 'en' => 'Work Order (SPK)'],
        'po' => ['id' => 'Purchase Order (PO)', 'en' => 'Purchase Order (PO)'],
        'moa' => ['id' => 'Memorandum of Agreement (MoA)', 'en' => 'Memorandum of Agreement (MoA)'],
        'job_invoice' => ['id' => 'Tagihan Pekerjaan', 'en' => 'Job Invoice for'],
        'period' => ['id' => 'Periode', 'en' => 'Period'],
        'draft' => ['id' => 'Draf (Belum Disetujui)', 'en' => 'Draft (Unapproved)'],
        'mr' => ['id' => 'Bapak', 'en' => 'Mr.'],
        'ms' => ['id' => 'Ibu', 'en' => 'Ms.'],
    ];

    // Simple English Number to Words (Terbilang) helper for English
    if ($lang === 'en' && !function_exists('numberToWordsEn')) {
        function numberToWordsEn($number)
        {
            $hyphen = '-';
            $conjunction = ' and ';
            $separator = ', ';
            $negative = 'negative ';
            $decimal = ' point ';
            $dictionary = [
                0 => 'zero',
                1 => 'one',
                2 => 'two',
                3 => 'three',
                4 => 'four',
                5 => 'five',
                6 => 'six',
                7 => 'seven',
                8 => 'eight',
                9 => 'nine',
                10 => 'ten',
                11 => 'eleven',
                12 => 'twelve',
                13 => 'thirteen',
                14 => 'fourteen',
                15 => 'fifteen',
                16 => 'sixteen',
                17 => 'seventeen',
                18 => 'eighteen',
                19 => 'nineteen',
                20 => 'twenty',
                30 => 'thirty',
                40 => 'forty',
                50 => 'fifty',
                60 => 'sixty',
                70 => 'seventy',
                80 => 'eighty',
                90 => 'ninety',
                100 => 'hundred',
                1000 => 'thousand',
                1000000 => 'million',
                1000000000 => 'billion',
                1000000000000 => 'trillion',
                1000000000000000 => 'quadrillion',
                1000000000000000000 => 'quintillion',
            ];

            if (!is_numeric($number)) {
                return false;
            }
            if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
                return false;
            }
            if ($number < 0) {
                return $negative . numberToWordsEn(abs($number));
            }

            $string = $fraction = null;
            if (strpos($number, '.') !== false) {
                [$number, $fraction] = explode('.', $number);
            }

            switch (true) {
                case $number < 21:
                    $string = $dictionary[$number];
                    break;
                case $number < 100:
                    $tens = ((int) ($number / 10)) * 10;
                    $units = $number % 10;
                    $string = $dictionary[$tens];
                    if ($units) {
                        $string .= $hyphen . $dictionary[$units];
                    }
                    break;
                case $number < 1000:
                    $hundreds = $number / 100;
                    $remainder = $number % 100;
                    $string = $dictionary[(int) $hundreds] . ' ' . $dictionary[100];
                    if ($remainder) {
                        $string .= $conjunction . numberToWordsEn($remainder);
                    }
                    break;
                default:
                    $baseUnit = pow(1000, floor(log($number, 1000)));
                    $numBaseUnits = (int) ($number / $baseUnit);
                    $remainder = $number % $baseUnit;
                    $string = numberToWordsEn($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                    if ($remainder) {
                        $string .= $remainder < 100 ? $conjunction : $separator;
                        $string .= numberToWordsEn($remainder);
                    }
                    break;
            }

            return $string;
        }
    }

    if ($lang === 'en') {
        $terbilangStr = ucwords(numberToWordsEn($record->total_amount)) . ' Rupiah';
    } else {
        $terbilangStr = trim(terbilang($record->total_amount)) . ' Rupiah';
    }

    $items = $record->getTranslation('items', $lang) ?? ($record->items ?? []);
    $tax_percentage =
        $record->tax_percentage ?? ($record->amount > 0 ? round(($record->tax_amount / $record->amount) * 100) : 11);
    $tax_wording =
        $record->getTranslation('tax_wording', $lang) ?? ($record->tax_wording ?? 'PPN ' . $tax_percentage . '%');

    $source = $record->sourceable;
    $sourceNumber = '-';
    $displaySourceType = $labels['source_doc'][$lang];
    $isInternal = false;
    $refNo = '-';
    $so = null;

    if ($source) {
        if ($source instanceof \Modules\CRM\Models\SalesOrder) {
            $so = $source;
            $isInternal = $source->type === \Modules\CRM\Enums\SalesOrderType::Internal;
            $sourceNumber = $isInternal ? '-' : $source->number;
            $displaySourceType = $isInternal ? $labels['source_internal'][$lang] : $labels['source_so'][$lang];
        } elseif ($source instanceof \Modules\Project\Models\WorkCompletionReport) {
            $refNo = $source->number;
            if ($source->sourceable instanceof \Modules\CRM\Models\SalesOrder) {
                $so = $source->sourceable;
            }
            $sourceNumber = $source->number;
            $displaySourceType = $lang === 'en' ? 'WCR' : 'BAPP';
        } elseif ($source instanceof \Modules\CRM\Models\PurchaseOrder) {
            $sourceNumber = $source->number;
            $displaySourceType = $labels['po'][$lang];
        } elseif ($source instanceof \Modules\CRM\Models\WorkOrder) {
            $sourceNumber = $source->number;
            $displaySourceType = $labels['spk'][$lang];
        } elseif ($source instanceof \Modules\CRM\Models\CooperationAgreement) {
            $sourceNumber = $source->number;
            $displaySourceType = $labels['pks'][$lang];
        }
    }

    $termOfPayment =
        $so?->payment_terms ??
        ($record->invoice_date && $record->due_date
            ? $record->invoice_date->diffInDays($record->due_date) . ' ' . $labels['days'][$lang]
            : '-');

    // Dynamic Payment Info
    $paymentInfo = is_array($record->payment_info)
        ? $record->payment_info
        : (json_decode($record->payment_info, true) ?? []);
    $accountName = strtoupper($paymentInfo['account_name'] ?? 'PT. GARUDA DAYA PRATAMA SEJAHTERA');
    $banks = $paymentInfo['banks'] ?? [];

    // Branding Assets
    $logoLogogram = imageToBase64(null, public_path('images/branding/header_left.png'));
    $logoDetail = imageToBase64(null, public_path('images/branding/header_right.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));

    // Customer Contact Logic
    $customerContactName = $record->content_config['recipient_name'] 
        ?? $so?->content_config['recipient_name'] 
        ?? null;
    $customerContactTitle = $record->content_config['recipient_title'] 
        ?? $so?->content_config['recipient_title'] 
        ?? null;
    $customerContactGender = $record->content_config['recipient_gender'] 
        ?? $so?->content_config['recipient_gender'] 
        ?? null;

    if ((!$customerContactName || !$customerContactTitle) && !empty($record->customer?->contacts)) {
        $firstContact = $record->customer->contacts[0];
        $customerContactName = $customerContactName ?: ($firstContact['name'] ?? null);
        $customerContactTitle = $customerContactTitle ?: ($firstContact['job_position'] ?? null);
        $customerContactGender = $customerContactGender ?: ($firstContact['gender'] ?? null);
    }

    $customerContactTitle = $customerContactTitle ?? '.....................';

    $salutation = '';
    if ($customerContactGender) {
        $salutation =
            $customerContactGender === 'male' ||
            $customerContactGender === \Modules\MasterData\Enums\Gender::Male->value
                ? $labels['mr'][$lang]
                : $labels['ms'][$lang];
    }

    $customerContactDisplay = $customerContactName
        ? ($salutation
            ? $salutation . ' ' . $customerContactName
            : $customerContactName)
        : $labels['accounting_unit'][$lang];

    $approverSig = $record->signatures()->where('signature_type', 'Approver')->first();
    $approverUnit = $approverSig
        ? ($approverSig->user->unit->name ?? $labels['accounting_unit'][$lang])
        : $labels['accounting_unit'][$lang];
@endphp

@php
    // Simple English Number to Words (Terbilang) helper for English
    if ($lang === 'en' && !function_exists('numberToWordsEn')) {
        function numberToWordsEn($number)
        {
            $hyphen = '-';
            $conjunction = ' and ';
            $separator = ', ';
            $negative = 'negative ';
            $decimal = ' point ';
            $dictionary = [
                0 => 'zero',
                1 => 'one',
                2 => 'two',
                3 => 'three',
                4 => 'four',
                5 => 'five',
                6 => 'six',
                7 => 'seven',
                8 => 'eight',
                9 => 'nine',
                10 => 'ten',
                11 => 'eleven',
                12 => 'twelve',
                13 => 'thirteen',
                14 => 'fourteen',
                15 => 'fifteen',
                16 => 'sixteen',
                17 => 'seventeen',
                18 => 'eighteen',
                19 => 'nineteen',
                20 => 'twenty',
                30 => 'thirty',
                40 => 'forty',
                50 => 'fifty',
                60 => 'sixty',
                70 => 'seventy',
                80 => 'eighty',
                90 => 'ninety',
                100 => 'hundred',
                1000 => 'thousand',
                1000000 => 'million',
                1000000000 => 'billion',
                1000000000000 => 'trillion',
                1000000000000000 => 'quadrillion',
                1000000000000000000 => 'quintillion',
            ];

            if (!is_numeric($number)) {
                return false;
            }
            if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
                return false;
            }
            if ($number < 0) {
                return $negative . numberToWordsEn(abs($number));
            }

            $string = $fraction = null;
            if (strpos($number, '.') !== false) {
                [$number, $fraction] = explode('.', $number);
            }

            switch (true) {
                case $number < 21:
                    $string = $dictionary[$number];
                    break;
                case $number < 100:
                    $tens = ((int) ($number / 10)) * 10;
                    $units = $number % 10;
                    $string = $dictionary[$tens];
                    if ($units) {
                        $string .= $hyphen . $dictionary[$units];
                    }
                    break;
                case $number < 1000:
                    $hundreds = $number / 100;
                    $remainder = $number % 100;
                    $string = $dictionary[(int) $hundreds] . ' ' . $dictionary[100];
                    if ($remainder) {
                        $string .= $conjunction . numberToWordsEn($remainder);
                    }
                    break;
                default:
                    $baseUnit = pow(1000, floor(log($number, 1000)));
                    $numBaseUnits = (int) ($number / $baseUnit);
                    $remainder = $number % $baseUnit;
                    $string = numberToWordsEn($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                    if ($remainder) {
                        $string .= $remainder < 100 ? $conjunction : $separator;
                        $string .= numberToWordsEn($remainder);
                    }
                    break;
            }

            return $string;
        }
    }

    if ($lang === 'en') {
        $terbilangStr = ucwords(numberToWordsEn($record->total_amount)) . ' Rupiah';
    }
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - {{ $record->number }}</title>
    <style>
        @page {
            margin: 1.5in 0.7in 1.5in 0.7in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
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
        }

        .header-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .header-table td {
            padding: 4px;
            vertical-align: top;
        }

        .box {
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 8px;
            background-color: #f9fafb;
        }

        .invoice-box {
            border: none;
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #1e3a8a;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .main-table th,
        .main-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }

        .main-table th {
            text-align: center;
            background-color: #f3f4f6;
            color: #374151;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 600;
        }

        .main-table .desc-col {
            padding: 12px 8px;
            vertical-align: top;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
            font-size: 12px;
        }

        .footer-table {
            width: 100%;
            margin-top: 25px;
        }

        .footer-table td {
            vertical-align: top;
        }

        .payment-box {
            border: 1px dashed #94a3b8;
            padding: 15px;
            line-height: 1.6;
            font-size: 11px;
            border-radius: 8px;
            background-color: #f8fafc;
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

        .text-primary {
            color: #1e40af;
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
            <div style="font-size: 8pt; color: #555; margin-bottom: 10px; text-align: center; width: 100%;">
                Soho Pancoran, Tower Splendor 2nd Floor Unit 207, Jl. Letjen MT. Haryono Kav. 2-3, Jakarta Selatan 12810
            </div>
            <img src="{{ $footerKop }}"
                style="width: 100%; height: auto; display: block; margin: 0; padding: 0; border: none; vertical-align: bottom;">
        @endif
        <div
            style="position: absolute; bottom: 30px; right: 50px; color: #ffffff; font-size: 9px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
            {{ $labels['page'][$lang] }} <span class="page-number"></span></div>
    </footer>

    <table class="header-table">
        <tr>
            <td width="20%">{{ $labels['invoice_no'][$lang] }}</td>
            <td width="35%">: {{ $record->number }}</td>
            <td width="20%">{{ $labels['top'][$lang] }}</td>
            <td width="25%">: {{ $termOfPayment }}</td>
        </tr>
        <tr>
            <td>{{ $displaySourceType }}</td>
            <td>: {{ $sourceNumber }}</td>
            <td>{{ $labels['invoice_date'][$lang] }}</td>
            <td>: {{ $record->invoice_date->locale($lang)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border-bottom: 1px solid #000;"></td>
            <td>{{ $labels['ref_no'][$lang] }}</td>
            <td style="border-bottom: 1px solid #000;">: {{ $refNo }}</td>
        </tr>
    </table>
    <br>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="box" style="height: 90px;">
                    <div style="margin-bottom: 5px;">{{ $labels['to'][$lang] }},</div>
                    <div style="margin-bottom: 3px;">{{ $customerContactDisplay }}</div>
                    @if ($customerContactTitle)
                        <div style="margin-bottom: 3px;">{{ $customerContactTitle }}</div>
                    @endif
                    <div class="font-bold" style="margin-bottom: 3px;">{{ $record->customer->name ?? '-' }}</div>
                    <div style="margin-bottom: 3px;">{{ $record->customer->address ?? 'Jakarta - Indonesia' }}</div>
                </div>
            </td>
            <td width="4%"></td>
            <td width="48%" style="vertical-align: middle;">
                <div class="invoice-box" style="height: 110px; display: table; width: 100%;">
                    <div style="display: table-cell; vertical-align: middle;">
                        {{ strtoupper($record->invoice_type ?? 'INVOICE') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">{{ $labels['table_no'][$lang] }}</th>
                <th width="40%">{{ $labels['table_desc'][$lang] }}</th>
                <th width="10%">{{ $labels['table_qty'][$lang] }}</th>
                <th width="10%">{{ $labels['table_unit'][$lang] }}</th>
                <th width="17%">{{ $labels['table_price'][$lang] }}</th>
                <th width="18%">{{ $labels['table_total'][$lang] }}</th>
            </tr>
        </thead>
        <tbody>
            @if (is_array($items) && count($items) > 0)
                @foreach ($items as $index => $item)
                    <tr>
                        <td class="desc-col text-center">{{ $index + 1 }}</td>
                        <td class="desc-col">{{ $item['item_name'] ?? 'Item ' . ($index + 1) }}</td>
                        <td class="desc-col text-center">{{ number_format($item['quantity'] ?? 0) }}</td>
                        <td class="desc-col text-center">{{ $item['uom'] ?? 'Unit' }}</td>
                        <td class="desc-col text-right">{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                        <td class="desc-col text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="desc-col text-center">1</td>
                    <td class="desc-col">
                        {{ $labels['job_invoice'][$lang] }}
                        {{ $record->salesOrder->project->name ?? 'Service' }}
                        {{ $labels['period'][$lang] }}
                        {{ $record->workCompletionReport ? $record->workCompletionReport->service_period_start->locale($lang)->translatedFormat('F Y') : $record->invoice_date->locale($lang)->translatedFormat('F Y') }}
                    </td>
                    <td class="desc-col text-center">1</td>
                    <td class="desc-col text-center">Ls</td>
                    <td class="desc-col text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
                    <td class="desc-col text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td width="60%" rowspan="4" style="vertical-align: top;">
                <div style="margin-bottom: 5px;">{{ $labels['terbilang'][$lang] }}:</div>
                <div style="font-size: 11px;">{{ $terbilangStr }}</div>
            </td>
            <td width="15%">{{ $labels['sub_total'][$lang] }}</td>
            <td width="25%" class="text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>
                {{ $labels['tax_base'][$lang] }}
                @if($record->tax_basis === 'management_fee')
                    (Fee)
                @endif
            </td>
            <td class="text-right">{{ $isInternal ? '-' : number_format($record->tax_base_amount ?? $record->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>{{ $isInternal ? '-' : $tax_wording }}</td>
            <td class="text-right">{{ $isInternal ? '-' : number_format($record->tax_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>{{ $labels['total'][$lang] }}</td>
            <td class="text-right">{{ number_format($record->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td width="60%">
                <div class="payment-box">
                    <div class="font-bold text-primary">{{ $labels['payment_instruction'][$lang] }}</div>
                    <div style="margin-top: 5px;">
                        <span class="font-bold">{{ $labels['an'][$lang] }} {{ $accountName }}</span><br>
                        @foreach ($banks as $bank)
                            <span class="font-bold">{{ $bank['bank_name'] ?? '' }} :
                                {{ $bank['account_number'] ?? '' }} ({{ $bank['currency'] ?? 'IDR' }})</span><br>
                        @endforeach
                    </div>
                </div>
            </td>
            <td width="40%" class="text-center" style="vertical-align: bottom;">
                <div>{{ $labels['an'][$lang] }} PT. Garuda Daya Pratama Sejahtera</div>

                <div style="height: 100px; margin: 10px 0; position: relative;">
                    @if ($approverSig)
                        <div
                            style="border: 2px dashed #4ade80; color: #166534; padding: 20px; font-weight: bold; width: 200px; margin: 0 auto;">
                            {{ $labels['signed_by'][$lang] }}<br>
                            {{ $approverSig->user->name }}<br>
                            <small>{{ $approverSig->signed_at->locale($lang)->translatedFormat('d M Y H:i') }}</small>
                        </div>
                    @else
                        <div
                            style="border: 2px dashed #9ca3af; color: #4b5563; padding: 20px; width: 200px; margin: 0 auto;">
                            {{ $labels['draft'][$lang] }}
                        </div>
                    @endif
                </div>

                <div class="font-bold" style="text-decoration: underline;">
                    {{ $approverSig ? $approverSig->user->name : 'Alvino Richardo Ali' }}
                </div>
                <div>Division Head of Accounting & Taxation</div>
            </td>
        </tr>
    </table>

</body>

</html>
