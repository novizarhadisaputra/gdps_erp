@php
    $pa = $record->profitabilityAnalysis ?? $record->lead?->latestProfitabilityAnalysis;
    $gi = $record->lead?->latestGeneralInformation;
    $customerName = $record->customer->name ?? '........';
    $productClusterName =
        $pa->productCluster->name ??
        ($gi->productCluster->name ?? ($record->lead->productCluster->name ?? 'Integrated Facility Management'));
    $location = $pa->projectArea->name ?? ($gi->location ?? '-');
    $revenue = $pa ? $pa->revenue_per_month : $record->amount;

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
    $logoSquare = imageToBase64(null, public_path('images/logo.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));
    $clusterLogoBase64 = null;

    // Config values
    $config = $record->content_config ?? [];
    $validityPeriod = $config['validity_period'] ?? 30;
    $paymentTerm = $config['payment_term'] ?? ($pa->paymentTerm->name ?? ($record->lead?->paymentTerm->name ?? '60'));
    $contactPhone = $config['contact_phone'] ?? null;
    $bookingCode = $config['booking_code'] ?? null;

    $recipientName = $config['recipient_name'] ?? null;
    $recipientTitle = $config['recipient_title'] ?? null;
    $recipientGender = $config['recipient_gender'] ?? null;
    $recipientSalutation = '';

    if ($recipientGender) {
        // Handle both Enum object and raw string
        $genderValue = $recipientGender instanceof \UnitEnum ? $recipientGender->value : $recipientGender;
        $genderEnum = \Modules\MasterData\Enums\Gender::tryFrom($genderValue);

        if ($genderEnum) {
            $recipientSalutation = $genderEnum->getSalutation() . ' ';
        } else {
            // Manual fallback mapping if Enum fails
            $recipientSalutation = match (strtolower($genderValue)) {
                'male' => 'Bapak ',
                'female' => 'Ibu ',
                default => '',
            };
        }
    }

    $introText = $config['intro_text'] ?? null;
    $closingText = $config['closing_text'] ?? null;

    $hasIntroText = !empty(trim(strip_tags($introText)));
    $hasClosingText = !empty(trim(strip_tags($closingText)));

    $ams = $record->lead->ams ?? ($record->lead->user ?? null);
    $amsName = $ams->name ?? 'Account Manager';
    $amsEmail = $ams->email ?? '';
    $amsPhone = $ams->phone_number ?? ($ams->phone ?? '');
    $contactPhone = $config['contact_phone'] ?? null;

    // Management fee
    $managementFee = $pa->management_fee_rate ?? 0;

    // Tools / Equipment
    $financials = $pa ? $pa->financial_assumptions : null;
    $operationalCosts = $financials['operational_costs'] ?? [];

    // Manpower
    $manpower = $pa ? $pa->manpower_requirements : [];
    $totalManpower = collect($manpower)->sum('quantity');

    $showManpower = $config['show_manpower_attachment'] ?? false;
    $showMaterial = $config['show_material_attachment'] ?? false;

    // Resolve template attachments from PA analysis_details
    $manpowerTemplateId = $pa->analysis_details['manpower_template_id'] ?? null;
    $costingTemplateId = $pa->analysis_details['costing_template_id'] ?? null;

    $manpowerTemplate = $manpowerTemplateId ? \Modules\CRM\Models\ManpowerTemplate::find($manpowerTemplateId) : null;
    $costingTemplate = $costingTemplateId ? \Modules\CRM\Models\CostingTemplate::find($costingTemplateId) : null;

    $manmanMedia = $manpowerTemplate?->getFirstMedia('source_file');
    $costMedia = $costingTemplate?->getFirstMedia('source_file');

    $manpowerSourceUrl = $manmanMedia ? $manmanMedia->getTemporaryUrl(now()->addDays(7)) : null;
    $costingSourceUrl = $costMedia ? $costMedia->getTemporaryUrl(now()->addDays(7)) : null;

    // Professional Case Formatting (Pascal/Title Case)
    $amsNameFormatted = \Illuminate\Support\Str::title($amsName);
    $recipientNameFormatted = \Illuminate\Support\Str::title($recipientName ?? '');
    $customerNameFormatted = \Illuminate\Support\Str::title($customerName);
    $productClusterNameFormatted = \Illuminate\Support\Str::title($productClusterName);

    // Meeting Date Handling
    $meetingDateText = $record->meeting_date ? $record->meeting_date->translatedFormat('d F Y') : '...................';
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Proposal - {{ $record->proposal_number }}</title>
    <style>
        @page {
            margin: 1.5in 0.7in 1.5in 0.7in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
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
            /* Locked to A4 width for full-bleed pattern */
            line-height: 0;
            z-index: 1000;
        }

        h1 {
            font-size: 14px;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 5px;
        }

        h2 {
            font-size: 12px;
            color: #1e293b;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        p {
            margin-bottom: 10px;
            text-align: justify;
        }

        ul,
        ol {
            margin-top: 0;
            margin-bottom: 10px;
            padding-left: 20px;
            text-align: justify;
        }

        li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Clean up RichEditor output inside lists */
        li p {
            margin: 0;
            display: inline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bg-gray {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .bg-dark {
            background-color: #0f172a;
            color: white;
            border-color: #0f172a;
        }

        .font-bold {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }

        .signature-space {
            height: 80px;
        }

        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .letter-info {
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .letter-info table {
            margin-bottom: 0;
            border: none;
        }

        .letter-info td {
            border: none;
            padding: 2px 5px 2px 0;
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

    <div class="content">
        <div class="letter-info">
            <div style="text-align: right; margin-bottom: 20px;">
                Tangerang,
                {{ $record->submission_date ? $record->submission_date->format('d F Y') : now()->format('d F Y') }}
            </div>
            <table style="width: auto; border: none; margin-bottom: 0;">
                <tr>
                    <td style="width: 70px; padding-bottom: 5px; border: none;"><strong>Nomor</strong></td>
                    <td style="padding-bottom: 5px; border: none;">: {{ $record->proposal_number }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 5px; border: none;"><strong>Lampiran</strong></td>
                    <td style="padding-bottom: 5px; border: none;">: 1 (Satu) Berkas</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; border: none;"><strong>Perihal</strong></td>
                    <td style="vertical-align: top; border: none;">: <strong>Proposal Penawaran Harga Layanan
                            {{ $productClusterNameFormatted }}</strong></td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px;">
            Yth.<br>
            @if ($recipientName)
                <strong>{{ $recipientSalutation }}{{ $recipientNameFormatted }}</strong>
                @if ($recipientTitle)
                    <span>({{ $recipientTitle }})</span>
                @endif
                <br>
            @elseif ($recipientTitle)
                <strong>{{ $recipientTitle }}</strong><br>
            @endif
            <strong>PT {{ $customerNameFormatted }}</strong><br>
            {{ $record->customer->address ?? 'Di Tempat' }}
        </div>


        <p>Dengan hormat,</p>
        <p>Semoga {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }} dalam keadaan sehat dan sukses dalam menjalankan
            aktivitas sehari-hari.</p>

        @if ($hasIntroText)
            <div style="margin-bottom: 10px;">{!! $introText !!}</div>
        @else
            <p>Kami dari PT Garuda Daya Pratama Sejahtera (PT GDPS) mengucapkan terima kasih atas kesempatan yang
                diberikan sehingga dapat menyampaikan Proposal Penawaran Harga Paket Layanan
                <strong>{{ $productClusterNameFormatted }}</strong> dengan nomor
                <strong>{{ $record->number }}</strong>
                sebagai tindak lanjut atas permintaan {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }} melalui
                email/telepon/online meeting/offline meeting/chat pada tanggal {{ $meetingDateText }}.
            </p>
            <p>Besar harapan kami supaya dapat menyampaikan klarifikasi secara langsung atas penawaran kami ini untuk
                memastikan bahwa kebutuhan perusahaan {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }} terpenuhi dengan
                baik.</p>
        @endif

        <p>Untuk penjelasan lebih rinci terkait spesifikasi teknis dan komersial, bersama surat pengantar ini kami
            lampirkan
            dokumen pendukung penawaran dan Rencana Anggaran Biaya (RAB) layanan secara komprehensif.</p>

        <div class="signature-section">
            <div class="signature-box" style="text-align: left; width: 60%;">
                <p>Atas perhatian dan kerja sama {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }}, kami ucapkan terima
                    kasih.</p>
                <br>
                <div>Hormat kami,</div>
                <div><strong>PT Garuda Daya Pratama Sejahtera</strong></div>
                <div class="signature-space"></div>
                <div><strong style="text-decoration: underline;">{{ $amsNameFormatted }}</strong></div>
                <div>Account Manager & Sales</div>
                @if ($amsEmail)
                    <div style="font-size: 10px;">{{ $amsEmail }}</div>
                @endif
                @if ($amsPhone)
                    <div style="font-size: 10px;">{{ $amsPhone }}</div>
                @endif
                @if ($contactPhone)
                    <div style="font-size: 10px; color: #1e293b; margin-top: 2px;">
                        {{ $contactPhone }}
                    </div>
                @endif
            </div>
        </div>

        <div class="page-break"></div>
        {{-- Section 1 Summary Table removed per layout optimization --}}

        @php
            $directItems = $pa ? $pa->getDirectItems() : collect();
            // Show detailed list if it's NOT manual cost OR if it IS manual cost but has items
$hasDetailedLists = $pa && $directItems->isNotEmpty();
$totalBaseCost = 0;
$feeAmount = 0;

if ($hasDetailedLists) {
    $totalBaseCost = $directItems->sum('total_monthly_cost');
                $feeAmount = max(0, $revenue - $totalBaseCost);
            }
        @endphp

        @if ($hasDetailedLists && $totalBaseCost > 0)
            <h2 style="margin-top: 20px; font-size: 14px; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; padding-bottom: 5px;">
                I. DETAIL PENAWARAN HARGA (ITEMIZED COST)
            </h2>
            <table style="font-size: 9px; table-layout: auto;">
                <thead>
                    <tr>
                        <th style="width: 3%;">No</th>
                        <th style="width: 25%;">Item Layanan</th>
                        <th style="width: 14%;">Harga Satuan (Rp)</th>
                        <th style="width: 5%;">Qty</th>
                        <th style="width: 8%;">UoM</th>
                        <th style="width: 15%;">Total Biaya (Rp)</th>
                        <th style="width: 13%;">Mgmt. Fee {{ number_format($managementFee, 0) }}% (Rp)</th>
                        <th style="width: 17%;">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumGrandTotal = 0;
                        $idx = 1;
                    @endphp
                    @foreach ($directItems as $item)
                        @php
                            $name = $item->name ?? 'Layanan Pendukung';
                            $price = $item->unit_cost_price ?? 0;
                            $qty = $item->quantity ?? 1;
                            $uom = $item->uom ?? 'Unit';
                            $totalCost = $item->total_monthly_cost ?? 0;
                            $proportionalFee = $totalBaseCost > 0 ? ($totalCost / $totalBaseCost) * $feeAmount : 0;
                            $subtotalItem = $totalCost + $proportionalFee;
                            $sumGrandTotal += $subtotalItem;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx++ }}</td>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ number_format($price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $qty }}</td>
                            <td class="text-center">{{ $uom }}</td>
                            <td class="text-right">{{ number_format($totalCost, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($proportionalFee, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotalItem, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray">
                        <td colspan="7" class="text-right font-bold" style="padding: 6px;">SUBTOTAL (DPP)</td>
                        <td class="text-right font-bold" style="padding: 6px;">
                            {{ number_format($sumGrandTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="text-right" style="padding: 6px;">PPN (11%)</td>
                        <td class="text-right" style="padding: 6px;">
                            {{ number_format($sumGrandTotal * 0.11, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-dark font-bold border-dark">
                        <td colspan="7" class="text-right"
                            style="color: white; background-color: #0f172a; padding: 10px;">
                            GRAND TOTAL PER BULAN (INCL. PPN)</td>
                        <td class="text-right" style="color: white; background-color: #0f172a; padding: 10px;">
                            {{ number_format($sumGrandTotal * 1.11, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="no-break">
            <h2>A. Ketentuan harga paket di atas mencakup:</h2>
            <ol>
                @if (!empty($config['included_items']))
                    @foreach ($config['included_items'] as $item)
                        <li>{!! $item['item'] ?? '' !!}</li>
                    @endforeach
                @else
                    @if ($totalManpower > 0)
                        <li>Personil berjumlah {{ $totalManpower }} orang.</li>
                    @else
                        <li>Rincian personil sesuai dengan kesepakatan kebutuhan operasional lapangan.</li>
                    @endif
                    <li>Personil yang ditugaskan sebagai PKWT telah memenuhi UU Ketenagakerjaan dan peraturan terkait
                        lainnya yang berlaku.</li>
                    <li>Remunerasi gaji pokok memakai UMK/UMP {{ now()->year }} setempat. Harga akan dievaluasi jika
                        ada
                        kebijakan baru dari pemerintah setempat.</li>
                    <li>Tunjangan Hari Raya (THR) proporsional.</li>
                    <li>Imbalan Pasca Kerja / Kompensasi sesuai regulasi.</li>
                    <li>ID Card, Seragam 2 setel, & Sepatu Safety 1 pasang (sesuai standar).</li>
                    <li>Alat Pelindung Diri (APD) penunjang pekerjaan.</li>
                    <li>On site training berkala guna mempertahankan kualitas layanan.</li>
                    <li>Medical Check-up berkala sesuai risiko pekerjaan.</li>
                    <li>BPJS Kesehatan dan BPJS Ketenagakerjaan (JKK, JKM, JHT, JP).</li>
                    <li>Pajak Penghasilan (PPh 21) bagi tenaga kerja.</li>
                    <li>Pajak Penghasilan (PPh 23) atas jasa manajemen.</li>
                    <li>Management fee sebesar {{ $managementFee }}%.</li>
                @endif
            </ol>
        </div>

        <div class="no-break" style="margin-top: 15px;">
            <h2>B. Complimentary harga paket di atas adalah sebagai berikut:</h2>
            <ol>
                @if (!empty($config['complimentary_items']))
                    @foreach ($config['complimentary_items'] as $item)
                        <li>{!! $item['item'] ?? '' !!}</li>
                    @endforeach
                @else
                    <li>Gratis implementasi aplikasi <strong>Presensi Pintar (Prespin)</strong> oleh GDPS.</li>
                    <li>Gratis implementasi aplikasi <strong>Integrated Facility Management (IFM Core)</strong> (untuk
                        cluster IFM).</li>
                    <li>Gratis implementasi aplikasi <strong>Integrated Security Management (ISM)</strong> (untuk
                        cluster
                        Security).</li>
                @endif
            </ol>
        </div>

        <div class="no-break" style="margin-top: 15px;">
            <h2>C. Harga di atas tidak termasuk:</h2>
            <ol>
                @if (!empty($config['excluded_items']))
                    @foreach ($config['excluded_items'] as $item)
                        <li>{!! $item['item'] ?? '' !!}</li>
                    @endforeach
                @else
                    <li>Manpower tambahan untuk mendukung kegiatan di luar pekerjaan paket.</li>
                    <li>Pass masuk khusus dan pelatihan kerja tambahan di luar lokasi kerja.</li>
                    <li>Insentif kerja di hari raya keagamaan (Idul Fitri, Natal, dsb) sebesar Rp 125.000,00/Hari
                        ditambah
                        management fee.</li>
                    <li>Biaya koordinasi wilayah (jika ada).</li>
                    <li>Recurrent training khusus permintaan pelanggan.</li>
                    <li>Overtime (Lembur). Tarif overtime mengacu pada rumus regulasi pemerintah:</li>
                @endif
            </ol>

            @if (empty($config['excluded_items']))
                <table style="width: 70%; margin-left: 20px;">
                    <tr class="bg-gray">
                        <th>Waktu (Weekday)</th>
                        <th>Perhitungan</th>
                    </tr>
                    <tr>
                        <td>1 Jam Pertama</td>
                        <td class="text-center">(Gapok / 173) x 1,5</td>
                    </tr>
                    <tr>
                        <td>Jam Kedua dst</td>
                        <td class="text-center">(Gapok / 173) x 2</td>
                    </tr>
                </table>
            @endif
        </div>

        <div class="page-break"></div>

        <div style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">2. Lain-lain</div>
        <ol type="a" style="margin-top: 0; margin-bottom: 15px;">
            @if (!empty($config['miscellaneous_items']))
                @foreach ($config['miscellaneous_items'] as $item)
                    <li style="margin-bottom: 4px;">{!! $item['item'] ?? '' !!}</li>
                @endforeach
            @else
                <li style="margin-bottom: 4px;"><i>Machinery, Equipment, Chemicals, & Consumable</i> terlampir. Jika ada
                    pengadaan <i>Machinery, Equipment, Chemicals, & Consumable</i> selain yang ada di daftar tersebut,
                    maka
                    akan dilakukan <i>reimbursement</i> ke PT {{ $customerName }}.</li>
                <li style="margin-bottom: 4px;">PT {{ $customerName }} menyediakan tempat kerja dan tempat penyimpanan
                    <i>Machinery, Equipment, Chemicals, & Consumable</i> tersebut.
                </li>
                <li style="margin-bottom: 4px;">Untuk dilakukan diskusi dan evaluasi lebih lanjut.</li>
            @endif
        </ol>

        <div style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">3. Term of Payment</div>
        <p style="margin-left: 20px; margin-bottom: 15px;"><i>Term of Payment</i> (TOP) <strong>{{ $paymentTerm }}
                hari</strong> setelah diterimanya Invoice.</p>

        @php
            $startDate = $pa?->start_date
                ? \Carbon\Carbon::parse($pa->start_date)->translatedFormat('d F Y')
                : '..................';
            $endDate = $pa?->end_date
                ? \Carbon\Carbon::parse($pa->end_date)->translatedFormat('d F Y')
                : '..................';
        @endphp
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">4. Periode Pekerjaan</div>
        <p style="margin-left: 20px; margin-bottom: 15px;">Periode Pekerjaan <strong>{{ $startDate }} –
                {{ $endDate }}</strong>.</p>

        <div style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">5. Masa Berlaku Proposal</div>
        <p style="margin-left: 20px; margin-bottom: 30px;">Proposal ini berlaku <strong>{{ $validityPeriod }}
                ({{ $validityPeriod == 30 ? 'tiga puluh' : $validityPeriod }}) hari</strong> sejak ditandatangani oleh
            GDPS.</p>

        @if ($hasClosingText)
            <div style="margin-top: 10px; margin-bottom: 30px;">{!! $closingText !!}</div>
        @else
            <p style="margin-top: 10px; margin-bottom: 30px;">Demikian proposal penawaran ini kami sampaikan. Atas
                perhatian dan kerja samanya kami ucapkan terima kasih.</p>
        @endif

        <div class="no-break" style="margin-top: 20px; padding-left: 20px; padding-right: 20px;">
            <table style="width: 100%; border: none; margin: 0; table-layout: fixed;">
                <tr>
                    <td style="border: none; width: 50%; padding: 0; text-align: left;">Diajukan oleh,</td>
                    <td style="border: none; width: 50%; padding: 0; text-align: left;">Disetujui oleh,</td>
                </tr>
                <tr>
                    <td style="border: none; width: 50%; padding: 25px 0 0 0; text-align: left; vertical-align: top;">
                        <div style="margin-bottom: 30px;">
                            <div style="height: 50px;"></div>
                        </div>
                        <div style="line-height: 1.4;">
                            <strong style="font-size: 12px;">{{ $amsNameFormatted }}</strong><br>
                            Account Manager & Sales<br>
                            PT Garuda Daya Pratama Sejahtera
                        </div>
                    </td>
                    <td style="border: none; width: 50%; padding: 25px 0 0 0; text-align: left; vertical-align: top;">
                        <div style="height: 50px; margin-bottom: 30px;"></div>
                        <div style="line-height: 1.4;">
                            <strong
                                style="font-size: 12px; border-bottom: 1px solid #1e293b;">{{ $recipientNameFormatted ?: '....................................' }}</strong><br>
                            {{ $recipientTitle ?? 'Jabatan' }}<br>
                            PT {{ $customerNameFormatted ?: '........................' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if ($pa && ($showManpower || $showMaterial))
            <div class="page-break"></div>
            <h1>LAMPIRAN: RINCIAN KOMPONEN (RAB / COGS)</h1>
            @if ($pa->is_manual_cost || $manpowerSourceUrl || $costingSourceUrl)
                <div
                    style="margin-top: 30px; text-align: center; border: 1px solid #cbd5e1; padding: 40px; border-radius: 8px; background-color: #f8fafc;">
                    <h3 style="margin-bottom: 25px;">Dokumen Rincian RAB/COGS dilampirkan secara terpisah (Manual
                        Upload)</h3>
                    <p style="margin-bottom: 25px; font-size: 11px;">Silakan akses dokumen rincian biaya komprehensif
                        melalui tautan aman di bawah ini.<br>Tautan enkripsi ini berlaku selama <strong>7 hari
                            kalender</strong> sejak proposal diterbitkan demi keamanan data.</p>

                    <div style="margin-top: 20px;">
                        @if ($manpowerSourceUrl)
                            <div style="margin-bottom: 20px; display: inline-block; margin-right: 15px;">
                                <a href="{{ $manpowerSourceUrl }}"
                                    style="display: inline-block; padding: 12px 24px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                    Unduh Rincian Manpower Costing
                                </a>
                            </div>
                        @endif

                        @if ($costingSourceUrl)
                            <div style="margin-bottom: 20px; display: inline-block;">
                                <a href="{{ $costingSourceUrl }}"
                                    style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                    Unduh Rincian Tools & Material Costing
                                </a>
                            </div>
                        @endif

                        @if (!$manpowerSourceUrl && !$costingSourceUrl && $pa->getFirstMedia('cogs_source'))
                            @php
                                $fallbackMedia = $pa->getFirstMedia('cogs_source');
                                $fallbackUrl = $fallbackMedia->getTemporaryUrl(now()->addDays(7));
                            @endphp
                            <a href="{{ $fallbackUrl }}"
                                style="display: inline-block; padding: 12px 24px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                Unduh Lampiran COGS / RAB
                            </a>
                        @endif
                    </div>

                    @if (!$manpowerSourceUrl && !$costingSourceUrl && !$pa->getFirstMedia('cogs_source'))
                        <p style="color: #ef4444; font-style: italic; margin-top: 20px;">[Lampiran Dokumen COGS Belum
                            Tersedia di Sistem]</p>
                    @endif
                </div>
            @else
                @if ($showManpower && !empty($manpower))
                    <h2 style="margin-top: 20px;">I. RINCIAN MANPOWER (KOMPENSASI TENAGA KERJA)</h2>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 35%;">Jabatan & Spesialisasi Profesi</th>
                                <th style="width: 7%;">Qty</th>
                                <th style="width: 13%;">Satuan</th>
                                <th style="width: 15%;">UMK Basis / Bln (Rp)</th>
                                <th style="width: 25%;">Total Direct Cost / Bln (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($manpower as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item['job_position_name'] ?? '-' }}</td>
                                    <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-center">{{ $item['uom'] ?? 'Orang' }}</td>
                                    <td class="text-right">{{ number_format($item['unit_cost'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($item['total_monthly_cost'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray">
                                <td colspan="5" class="text-right">Subtotal Biaya Dasar Operasional Manpower /
                                    Bulan</td>
                                <td class="text-right">
                                    {{ number_format(collect($manpower)->sum('total_monthly_cost'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </table>
                @endif

                @if ($showMaterial && !empty($operationalCosts))
                    <h2 style="margin-top: 30px;">II. RINCIAN SEWA PERALATAN & MATERIAL KERJA (TOOLS/CONSUMABLE)</h2>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 35%;">Spesifikasi Item / Mesin / Chemical</th>
                                <th style="width: 7%;">Qty</th>
                                <th style="width: 13%;">Satuan</th>
                                <th style="width: 15%;">Nominal Unit (Rp)</th>
                                <th style="width: 25%;">Total Tarif/Bulan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operationalCosts as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item['item_name'] ?? '-' }}</td>
                                    <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-center">{{ $item['uom'] ?? 'Unit' }}</td>
                                    <td class="text-right">{{ number_format($item['unit_cost'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($item['total_monthly_cost'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray">
                                <td colspan="5" class="text-right">Subtotal Estimasi Pendanaan Peralatan Pendukung
                                    /
                                    Bulan
                                </td>
                                <td class="text-right">
                                    {{ number_format(collect($operationalCosts)->sum('total_monthly_cost'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </table>
                @endif

            @endif

            {{-- Section III removed per professional aesthetics requirement --}}
        @endif
    </div>
</body>

</html>
