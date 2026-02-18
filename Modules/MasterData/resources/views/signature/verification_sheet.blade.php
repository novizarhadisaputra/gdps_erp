<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen py-12 px-4 sm:px-6">
    <div class="max-w-3xl mx-auto">
        <!-- Main Card -->
        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-slate-200">
            <!-- Header -->
            <div class="p-8 sm:p-12 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white relative">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                    <div class="space-y-2">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 object-contain">
                        <div class="text-xs text-slate-500 uppercase tracking-widest font-semibold pt-2">
                            PT GARUDA DAYA PRATAMA SEJAHTERA
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <div
                            class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            VALID & ASLI
                        </div>
                        <div class="mt-2 text-[10px] text-slate-400 font-mono">
                            Checksum: {{ substr(md5($document->id . $signed_at), 0, 12) }}
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Lembar Verifikasi</h1>
                    <p class="text-slate-500 mt-1 max-w-sm mx-auto text-sm">Informasi Keaslian Tanda Tangan Elektronik
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 sm:p-12 space-y-10">
                <!-- Document Details -->
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                        <span class="mr-2">I.</span> Informasi Dokumen
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-12">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Nomor Registrasi</label>
                            <p class="text-slate-900 font-semibold">{{ $document->document_number ?? $document->id }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Tipe Dokumen</label>
                            <p class="text-slate-900 font-semibold">{{ class_basename($document) }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Pelanggan</label>
                            <p class="text-slate-900 font-semibold">{{ $document->customer->name ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Status Akhir</label>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-700">
                                {{ ucfirst($document->status->value ?? $document->status) }}
                            </span>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <!-- Statement -->
                <section class="bg-indigo-50/30 rounded-2xl p-6 border border-indigo-100/50">
                    <p class="text-sm leading-relaxed text-slate-700">
                        Dinyatakan <strong class="text-emerald-700">VALID</strong> dan <strong
                            class="text-slate-900">Telah Disetujui</strong> oleh PT. GARUDA DAYA PRATAMA SEJAHTERA dan
                        dengan ketentuan yang berlaku. Dokumen ini telah ditandatangani secara elektronik sehingga tidak
                        memerlukan tanda tangan basah.
                    </p>
                </section>

                <!-- Approval List -->
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                        <span class="mr-2">II.</span> Riwayat Persetujuan
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">No
                                    </th>
                                    <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Jabatan / Unit</th>
                                    <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Penandatangan</th>
                                    <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2">
                                        Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($signatures as $index => $sig)
                                    <tr class="{{ $signer->id === $sig->user_id ? 'bg-primary-50/50' : '' }}">
                                        <td class="py-4 text-sm text-slate-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="py-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $sig->role }}</div>
                                            <div class="text-[10px] text-slate-500">
                                                {{ $sig->user->unit->name ?? 'Internal' }}</div>
                                        </td>
                                        <td class="py-4">
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ $sig->user->name ?? 'Unknown' }}</div>
                                            @if ($signer->id === $sig->user_id)
                                                <span
                                                    class="text-[9px] font-bold text-primary-600 bg-primary-50 px-1 rounded border border-primary-100 uppercase">Dipindai</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-2">
                                            <div class="text-sm text-slate-900 font-medium">
                                                {{ $sig->signed_at->format('d M Y') }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                {{ $sig->signed_at->format('H:i:s') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50/80 p-8 sm:p-12 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-[10px] text-slate-400 text-center sm:text-left leading-relaxed max-w-sm">
                        Dicetak pada: {{ now()->format('d M Y H:i:s') }} WIB. Digunakan untuk keperluan verifikasi
                        internal dan eksternal PT. GDPS.
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-8">
            <a href="javascript:window.print()"
                class="inline-flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Cetak Lembar Ke Layar
            </a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}"
                class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-700 transition-colors uppercase tracking-widest bg-primary-50 px-4 py-2 rounded-full border border-primary-100 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Unduh PDF Resmi
            </a>
        </div>
    </div>
</body>

</html>
