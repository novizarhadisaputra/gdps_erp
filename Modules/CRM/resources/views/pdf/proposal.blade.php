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
            <table>
                <tr>
                    <td style="width: 80px; padding-bottom: 5px;"><strong>Nomor</strong></td>
                    <td style="padding-bottom: 5px;">: {{ $record->proposal_number }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 5px;"><strong>Lampiran</strong></td>
                    <td style="padding-bottom: 5px;">: 1 (Satu) Berkas</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Perihal</strong></td>
                    <td style="vertical-align: top;">: <strong>Proposal Penawaran Harga Layanan
                            {{ $productClusterName }}</strong></td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px;">
            Kepada Yth.,<br>
            @if ($recipientName)
                <strong>{{ $recipientSalutation }}{{ $recipientName }}</strong><br>
                @if ($recipientTitle)
                    <span>{{ $recipientTitle }}</span><br>
                @endif
            @endif
            <strong>PT {{ $customerName }}</strong><br>
            {{ $record->customer->address ?? 'Di Tempat' }}
        </div>


        <p>Dengan hormat,</p>
        <p>Semoga {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }} dalam keadaan sehat dan sukses dalam menjalankan
            aktivitas sehari-hari.</p>

        @if ($introText)
            <div style="margin-bottom: 10px;">{!! $introText !!}</div>
        @else
            <p>Memenuhi kebutuhan operasional pada perusahaan yang {{ trim($recipientSalutation) ?: 'Bapak/Ibu' }}
                pimpin, bersama ini kami PT Garuda Daya
                Pratama
                Sejahtera (GDPS) menyampaikan penghargaan dan terima kasih atas kesempatan yang diberikan untuk
                berpartisipasi dalam memberikan solusi layanan <strong>{{ $productClusterName }}</strong> di PT
                {{ $customerName }}.</p>
            <p>GDPS merupakan perusahaan afiliasi dari PT Garuda Indonesia (Persero) Tbk, yang fokus pada penyediaan
                solusi
                operasional layanan gedung, facility management, serta penyediaan sumber daya manusia yang berkualitas
                berbasis teknologi terdepan.</p>
            <p>Melalui proposal ini, kami mengajukan rancangan rincian biaya yang telah disesuaikan dengan prosedur,
                kebutuhan, serta kesepakatan bersama, dengan harapan dapat berkontribusi positif bagi efisiensi dan
                peningkatan kualitas pelayanan di lingkungan bisnis PT {{ $customerName }}.</p>
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
                <div><strong style="text-decoration: underline;">{{ $amsName }}</strong></div>
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
        <h1>1. HARGA & DETAIL LAYANAN</h1>

        @php
            $cluster = $pa->productCluster ?? ($gi->productCluster ?? ($record->lead?->productCluster ?? null));
            $clusterLogoBase64 = $cluster ? imageToBase64($cluster->getFirstMedia('logo')) : null;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 55%;">Deskripsi Layanan (Service Description)</th>
                    <th style="width: 15%;">Lokasi (Site)</th>
                    <th style="width: 30%;">Total Harga / Bulan (Price IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if ($clusterLogoBase64)
                            <img src="{{ $clusterLogoBase64 }}"
                                style="height: 35px; margin-bottom: 8px; display: block;">
                        @endif
                        <strong>Paket Layanan {{ $productClusterName }}</strong>
                        <div style="font-size: 9px; color: #475569; margin-top: 5px;">
                            Layanan operasional hulu ke hilir yang dikelola secara profesional sesuai standar aviasi
                            Garuda
                            Indonesia Group.
                        </div>
                    </td>
                    <td class="text-center">{{ $location }}</td>
                    <td class="text-right">{{ number_format($revenue, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray">
                    <td colspan="2" class="text-right">Subtotal (DPP)</td>
                    <td class="text-right">{{ number_format($revenue, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right">PPN (11%)</td>
                    <td class="text-right">{{ number_format($revenue * 0.11, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-dark font-bold border-dark">
                    <td colspan="2" class="text-right"
                        style="color: white; background-color: #0f172a; padding: 10px;">
                        GRAND TOTAL PER BULAN (INCL. PPN)</td>
                    <td class="text-right" style="color: white; background-color: #0f172a; padding: 10px;">
                        {{ number_format($revenue * 1.11, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

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

        <h1>2. SYARAT DAN KETENTUAN (TERMS & CONDITIONS)</h1>
        <ol>
            <li style="margin-bottom: 15px;">
                <strong>Masa Berlaku Proposal</strong><br>
                Proposal penawaran ini berlaku selama <strong>{{ $validityPeriod }}
                    ({{ $validityPeriod == 30 ? 'tiga puluh' : $validityPeriod }}) hari kalender</strong> terhitung
                sejak
                tanggal dokumen ini diterbitkan.
            </li>
            <li style="margin-bottom: 15px;">
                <strong>Termin Pembayaran (TOP)</strong><br>
                Pembayaran dilakukan dalam waktu <strong>{{ $paymentTerm }} hari kalender</strong> setelah invoice
                diterima secara lengkap dan benar (<i>back-to-back</i>).
            </li>
        </ol>

        @if ($closingText)
            <div style="margin-top: 10px;">{!! $closingText !!}</div>
        @endif

        <div class="no-break" style="margin-top: 40px;">
            <p
                style="text-align: center; font-style: italic; color: #475569; border: 1px dashed #cbd5e1; padding: 15px; border-radius: 5px;">
                "Dengan ditandatanganinya lembar persetujuan ini, PT {{ $customerName }} menyatakan sepakat atas draf
                harga
                dan ketentuan operasional yang tercantum dalam Proposal ini."
            </p>

            <div class="signature-section">
                <div class="signature-box">
                    <div style="margin-bottom: 10px;">Diajukan Oleh,<br><strong>PT Garuda Daya Pratama
                            Sejahtera</strong>
                    </div>
                    <div class="signature-space"></div>
                    <div><strong style="text-decoration: underline;">{{ $amsName }}</strong></div>
                    <div>Account Manager & Sales</div>
                </div>
                <div class="signature-box" style="float: right;">
                    <div style="margin-bottom: 10px;">Disetujui Oleh,<br><strong>PT {{ $customerName }}</strong></div>
                    <div class="signature-space"></div>
                    <div><strong
                            style="text-decoration: underline;">(............................................)</strong>
                    </div>
                    <div>Jabatan: ............................</div>
                    <div style="font-size: 10px; margin-top: 5px;">Tgl: ....................</div>
                </div>
                <div style="clear: both"></div>
            </div>
        </div>

        @if ($pa && ($showManpower || $showMaterial))
            <div class="page-break"></div>
            <h1>LAMPIRAN: RINCIAN KOMPONEN (RAB / COGS)</h1>

            @if ($pa->is_manual_cost)
                @php
                    $cogsMedia = $pa->getFirstMedia('cogs_source');
                    $expiringUrl = $cogsMedia ? $cogsMedia->getTemporaryUrl(now()->addDays(7)) : '#';
                @endphp
                <div
                    style="margin-top: 30px; text-align: center; border: 1px solid #cbd5e1; padding: 40px; border-radius: 8px; background-color: #f8fafc;">
                    <h3 style="margin-bottom: 15px;">Dokumen Rincian RAB/COGS dilampirkan secara terpisah (Manual
                        Upload)</h3>
                    @if ($cogsMedia)
                        <p style="margin-bottom: 20px; font-size: 11px;">Silakan akses dokumen rincian biaya
                            komprehensif melalui tautan aman di bawah ini.<br>Tautan enkripsi ini berlaku selama
                            <strong>7 hari kalender</strong> sejak proposal diterbitkan demi keamanan data.</p>
                        <a href="{{ $expiringUrl }}"
                            style="display: inline-block; padding: 10px 20px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            Unduh Lampiran COGS / RAB
                        </a>
                        <p
                            style="font-size: 8px; color: #94a3b8; font-style: italic; margin-top: 20px; word-wrap: break-word;">
                            URL Akses Raw: <br>
                            <a href="{{ $expiringUrl }}"
                                style="color: #64748b;">{{ \Illuminate\Support\Str::limit($expiringUrl, 80) }}</a>
                        </p>
                    @else
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
                    <p style="font-size: 9px; font-style: italic; color: #64748b; margin-top: -5px;">*Disclaimer:
                        Komponen
                        Cost
                        di atas adalah proyeksi harga riil yang disiapkan untuk menampung hak-hak hukum tenaga kerja,
                        termasuk
                        perlindungan dasar asuransi wajib jamsostek (BPJS Kesehatan, BPJS Ketenagakerjaan JHT, JP, JKM,
                        JKK),
                        proporsional THR (1 bulan upah per tahun kerja), cadangan Cuti Tahunan, kompensasi akhir kontrak
                        (PP
                        No.35/2021) jika relevan, hingga pembinaan sumber daya pelatihan secara berkelanjutan yang
                        diselenggarakan oleh vendor manajemen alih-daya.</p>
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
                    <p style="font-size: 9px; font-style: italic; margin-top: -5px; color: #64748b;">*Disclaimer:
                        Peralatan
                        alat kerja di atas mengikat disiapkan di titik-titik (Site/Pool) proyek yang disepakati untuk
                        menjamin
                        standardisasi mutu SLA pekerjaan. Seluruh nilai penyusutan alat berat maupun kelengkapan habis
                        pakai
                        (consumable chemical) sudah terverifikasi dari prinsip operasional pabrikan dan akan dikelola
                        secara
                        penuh oleh Tim Pemeliharaan Aset PT Garuda Daya Pratama Sejahtera.</p>
                @endif

            @endif

            {{-- Summary of Component Recap --}}
            <h2 style="margin-top: 30px;">III. REKAPITULASI KOMPONEN BIAYA (SUMMARY COST)</h2>
            @php
                if ($pa->is_manual_cost) {
                    $totalBaseCost = $pa->direct_cost;
                } else {
                    $totalBaseCost =
                        collect($manpower)->sum('total_monthly_cost') +
                        collect($operationalCosts)->sum('total_monthly_cost');
                }
                $feeAmount = $revenue - $totalBaseCost;
            @endphp
            <table style="width: 100%;">
                <tr class="bg-gray">
                    <th style="width: 75%; text-align: left;">Deskripsi Komponen (Cost Description)</th>
                    <th style="width: 25%; text-align: right;">Total Nilai / Bulan (Rp)</th>
                </tr>
                <tr>
                    <td>Total Direct Cost (Personil & Material)</td>
                    <td class="text-right">{{ number_format($totalBaseCost, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Management Fee GDPS ({{ number_format($managementFee, 0) }}%)</td>
                    <td class="text-right">{{ number_format($feeAmount, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-dark" style="color: white; font-weight: bold;">
                    <td class="text-right">ESTIMASI TOTAL HARGA PENAWARAN (DPP)</td>
                    <td class="text-right">{{ number_format($revenue, 0, ',', '.') }}</td>
                </tr>
            </table>
        @endif
    </div>
</body>

</html>
