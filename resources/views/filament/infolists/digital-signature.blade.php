<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @if (is_array($getState) || is_object($getState))
        @foreach ($getState as $signature)
            @php
                // Decode QR Data if it's stored as JSON string (it's generated as JSON encoded string in SignatureService)
                // But the service returns SVG string for generatedQRCode.
                // The 'signatures' column in DB acts as the state.
                // The record usually stores: ['role' => '...', 'qr' => 'SVG...', 'user_id' => '...', 'signed_at' => '...']

                // Wait, let's check how it's stored.
                // In GeneralInformationTable: $record->addSignature($user, 'approved', $qrCode);
                // addSignature usually appends: ['user_id' => $user->id, 'role' => $role, 'qr' => $qrCode, 'signed_at' => now()]
                // So $signature is that array.

                $user = \App\Models\User::find($signature['user_id'] ?? null);
                $signatureImage = $user ? $user->getFirstMediaUrl('signature') : null;
            @endphp

            <div class="border rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm flex flex-col items-center space-y-3">
                <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                    {{ $signature['user_role'] ?? 'Signer' }}
                </div>

                @if ($signatureImage)
                    <img src="{{ $signatureImage }}" alt="Signature" class="h-16 object-contain" />
                @else
                    <div class="h-16 flex items-center justify-center text-gray-400 italic">
                        No Signature Image
                    </div>
                @endif

                <div class="text-xs text-gray-500">
                    {{ $signature['user_name'] ?? ($user->name ?? 'Unknown User') }}
                </div>

                <div class="w-24 h-24">
                    {!! $signature['qr_code'] ?? '' !!}
                </div>

                <div class="text-[10px] text-gray-400">
                    {{ \Carbon\Carbon::parse($signature['signed_at'] ?? now())->format('d M Y H:i') }}
                </div>
            </div>
        @endforeach
    @else
        <div class="text-gray-500 text-sm italic">No signatures yet.</div>
    @endif
</div>
