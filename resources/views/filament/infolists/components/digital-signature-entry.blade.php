<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $signatures = $getSignatures();
                $service = app(\Modules\MasterData\Services\SignatureService::class);
            @endphp

            @if ($signatures->isNotEmpty())
                @foreach ($signatures as $signature)
                    @php
                        $user = $signature->user;
                        $userName = $user ? $user->name : 'Unknown User';

                        $qrCodeSvg = null;
                        try {
                            $qrData = $service->createSignatureData(
                                $user,
                                $getRecord(),
                                $signature->signature_type ?? 'approved',
                            );
                            $qrCodeSvg = $service->generateQRCode($qrData);
                        } catch (\Exception $e) {
                            $qrCodeSvg = null;
                        }
                    @endphp

                    <div
                        class="relative group border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-all duration-300">
                        {{-- Decorative side bar --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary-600 rounded-l-xl opacity-75"></div>

                        <div class="p-4 flex items-center space-x-4">
                            {{-- Left Column: QR Code --}}
                            <div
                                class="flex-shrink-0 w-20 h-20 bg-gray-50 dark:bg-white/5 rounded-lg p-1.5 border border-gray-100 dark:border-gray-800">
                                @if ($qrCodeSvg)
                                    <img src="{{ $qrCodeSvg }}" alt="QR Code" class="w-full h-full object-contain" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-heroicon-o-qr-code class="w-8 h-8 text-gray-300" />
                                    </div>
                                @endif
                            </div>

                            {{-- Right Column: User Info & Verification --}}
                            <div class="flex-grow min-w-0 flex flex-col justify-between h-20">
                                <div>
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span
                                            class="text-[9px] font-bold uppercase tracking-wider text-primary-600 px-1.5 py-0.5 rounded">
                                            {{ $signature->role }}
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $userName }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div
                    class="col-span-full border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl p-8 flex flex-col items-center justify-center bg-gray-50/50 dark:bg-gray-900/20">
                    <x-heroicon-o-pencil-square class="w-10 h-10 text-gray-300 mb-3" />
                    <span class="text-sm text-gray-500 font-medium">Wait for signatures...</span>
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
