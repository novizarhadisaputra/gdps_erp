@php
    if (!function_exists('terbilang')) {
        function terbilang($x) {
            $angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            if ($x < 12) return " " . $angka[$x];
            elseif ($x < 20) return terbilang($x - 10) . " Belas";
            elseif ($x < 100) return terbilang($x / 10) . " Puluh" . terbilang($x % 10);
            elseif ($x < 200) return "Seratus" . terbilang($x - 100);
            elseif ($x < 1000) return terbilang($x / 100) . " Ratus" . terbilang($x % 100);
            elseif ($x < 2000) return "Seribu" . terbilang($x - 1000);
            elseif ($x < 1000000) return terbilang($x / 1000) . " Ribu" . terbilang($x % 1000);
            elseif ($x < 1000000000) return terbilang($x / 1000000) . " Juta" . terbilang($x % 1000000);
            elseif ($x < 1000000000000) return terbilang($x / 1000000000) . " Milyar" . terbilang(fmod($x, 1000000000));
            elseif ($x < 1000000000000000) return terbilang($x / 1000000000000) . " Trilyun" . terbilang(fmod($x, 1000000000000));
            return "";
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
                if (!$content) return null;
                return 'data:image/' . $extension . ';base64,' . base64_encode($content);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    $terbilangStr = trim(terbilang($record->total_amount)) . " Rupiah";
    $tax_percentage = $record->amount > 0 ? round(($record->tax_amount / $record->amount) * 100) : 12;

    $approverSig = $record->signatures()->where('signature_type', 'Approver')->first();
    
    // Calculate Term of Payment
    $termOfPayment = $record->salesOrder->payment_terms ?? ($record->invoice_date && $record->due_date ? $record->invoice_date->diffInDays($record->due_date) . ' Hari' : '-');

    // Dynamic Payment Info
    $paymentInfo = is_array($record->payment_info) ? $record->payment_info : json_decode($record->payment_info, true) ?? [];
    $accountName = $paymentInfo['account_name'] ?? '';
    $banks = $paymentInfo['banks'] ?? [];

    // Branding Assets
    $logoLogogram = imageToBase64(null, public_path('images/branding/header_left.png'));
    $logoDetail = imageToBase64(null, public_path('images/branding/header_right.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));

    // Customer Contact Logic
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

    $customerContactDisplay = $customerContactName ? ($salutation ? $salutation . ' ' . $customerContactName : $customerContactName) : 'Unit Accounting';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - {{ $record->invoice_number }}</title>
    <style>
        @page {
            margin: 1.5in 0.7in 1.5in 0.7in;
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #000; line-height: 1.3; margin: 0; padding: 0; }
        
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
        footer .page-number:after { content: counter(page); }

        table { width: 100%; border-collapse: collapse; }
        .header-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .header-table td { padding: 4px; vertical-align: top; }
        
        .box { border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; background-color: #f9fafb; }
        .invoice-box { border: none; text-align: center; font-size: 26px; font-weight: bold; letter-spacing: 4px; color: #1e3a8a; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .main-table th, .main-table td { padding: 8px; border: 1px solid #e5e7eb; }
        .main-table th { text-align: center; background-color: #f3f4f6; color: #374151; border-bottom: 2px solid #cbd5e1; font-weight: 600; }
        .main-table .desc-col { padding: 12px 8px; vertical-align: top; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
        .summary-table tr:last-child td { border-bottom: none; font-weight: bold; background-color: #f8fafc; border-top: 2px solid #cbd5e1; font-size: 12px; }
        
        .footer-table { width: 100%; margin-top: 25px; }
        .footer-table td { vertical-align: top; }
        
        .payment-box { border: 1px dashed #94a3b8; padding: 15px; line-height: 1.6; font-size: 11px; border-radius: 8px; background-color: #f8fafc; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-primary { color: #1e40af; }
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

    <table class="header-table">
        <tr>
            <td width="20%">No Invoice</td>
            <td width="35%">: {{ $record->invoice_number }}</td>
            <td width="20%">Term of Payment</td>
            <td width="25%">: {{ $termOfPayment }}</td>
        </tr>
        <tr>
            <td>No PO / No. Kontrak</td>
            <td>: {{ $record->salesOrder->so_number ?? '-' }}</td>
            <td>Tanggal Invoice</td>
            <td>: {{ $record->invoice_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border-bottom: 1px solid #000;"></td>
            <td>No Ref</td>
            <td style="border-bottom: 1px solid #000;">: {{ $record->workCompletionReport->report_number ?? '-' }}</td>
        </tr>
    </table>
    <br>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="box" style="height: 90px;">
                    <div style="margin-bottom: 5px;">Kepada YTH :</div>
                    <div style="margin-bottom: 3px;">{{ $customerContactDisplay }}</div>
                    @if($customerContactTitle)
                        <div style="margin-bottom: 3px;">{{ $customerContactTitle }}</div>
                    @endif
                    <div class="font-bold" style="margin-bottom: 3px;">{{ $record->customer->name ?? '-' }}</div>
                    <div style="margin-bottom: 3px;">{{ $record->customer->address ?? 'Jakarta - Indonesia' }}</div>
                </div>
            </td>
            <td width="4%"></td>
            <td width="48%" style="vertical-align: middle;">
                <div class="invoice-box" style="height: 110px; display: table; width: 100%;">
                    <div style="display: table-cell; vertical-align: middle;">INVOICE</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="55%">Keterangan</th>
                <th width="15%">Unit</th>
                <th width="25%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @if(is_array($record->items) && count($record->items) > 0)
                @foreach($record->items as $index => $item)
                <tr>
                    <td class="desc-col text-center">{{ $index + 1 }}</td>
                    <td class="desc-col">
                        {{ $item['item_name'] ?? 'Item ' . ($index + 1) }}
                    </td>
                    <td class="desc-col text-center">{{ $item['quantity'] ?? 1 }} {{ $item['uom'] ?? 'Ls' }}</td>
                    <td class="desc-col text-right">{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="desc-col text-center">1</td>
                    <td class="desc-col">
                        Tagihan Pekerjaan {{ $record->salesOrder->project->name ?? 'Service' }} Periode {{ $record->workCompletionReport ? $record->workCompletionReport->service_period_start->format('F Y') : $record->invoice_date->format('F Y') }}
                    </td>
                    <td class="desc-col text-center">1 Ls</td>
                    <td class="desc-col text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td width="60%" rowspan="4" style="vertical-align: top;">
                <div style="margin-bottom: 5px;">Terbilang:</div>
                <div style="font-size: 11px;">{{ $terbilangStr }}</div>
            </td>
            <td width="15%">Sub Total</td>
            <td width="25%" class="text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>DPP Nilai Lain</td>
            <td class="text-right">{{ number_format($record->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>PPN {{ $tax_percentage }}%</td>
            <td class="text-right">{{ number_format($record->tax_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total</td>
            <td class="text-right">{{ number_format($record->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td width="60%">
                <div class="payment-box">
                    <div class="font-bold text-primary">Pembayaran dapat dilakukan melalui transfer ke rekening kami :</div>
                    <div style="margin-top: 5px;">
                        <span class="font-bold">a/n {{ $accountName }}</span><br>
                        @foreach($banks as $bank)
                            <span class="font-bold">{{ $bank['bank_name'] ?? '' }} : {{ $bank['account_number'] ?? '' }} ({{ $bank['currency'] ?? 'IDR' }})</span><br>
                        @endforeach
                    </div>
                </div>
            </td>
            <td width="40%" class="text-center" style="vertical-align: bottom;">
                <div>PT. Garuda Daya Pratama Sejahtera</div>
                
                <div style="height: 100px; margin: 10px 0; position: relative;">
                    @if($approverSig)
                        <div style="border: 2px dashed #4ade80; color: #166534; padding: 20px; font-weight: bold; width: 200px; margin: 0 auto;">
                            Digitally Signed By<br>
                            {{ $approverSig->user->name }}<br>
                            <small>{{ $approverSig->signed_at->format('d M Y H:i') }}</small>
                        </div>
                    @else
                        <div style="border: 2px dashed #9ca3af; color: #4b5563; padding: 20px; width: 200px; margin: 0 auto;">
                            Draft (Unapproved)
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
