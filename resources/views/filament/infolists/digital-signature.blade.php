<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @if (!empty($getState) && (is_array($getState) || is_object($getState)))
        @foreach ($getState as $signature)
            @php
                // Signature can be an array (if casted) or Model
                // Using object access style for Model
                $userId = $signature['user_id'] ?? ($signature->user_id ?? null);
                $userRole = $signature['role'] ?? ($signature->role ?? 'Signer');
                $qrCode = $signature['qr_code_path'] ?? ($signature->qr_code_path ?? '');
                $signedAt = $signature['signed_at'] ?? ($signature->signed_at ?? now());

                $user = \App\Models\User::find($userId);
                $userName = $user ? $user->name : $signature['user_name'] ?? 'Unknown User';
                $signatureImage = $user ? $user->getFirstMediaUrl('signature') : null;
            @endphp

            <div class="border rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm flex flex-col items-center space-y-3">
                <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                    {{ $userRole }}
                </div>

                @if ($signatureImage)
                    <img src="{{ $signatureImage }}" alt="Signature" class="h-16 object-contain" />
                @else
                    <div class="h-16 flex items-center justify-center text-gray-400 italic">
                        No Signature Image
                    </div>
                @endif

                <div class="text-xs text-gray-500">
                    {{ $userName }}
                </div>

                <div class="w-24 h-24">
                    {!! $qrCode !!}
                </div>

                <div class="text-[10px] text-gray-400">
                    {{ \Carbon\Carbon::parse($signedAt)->format('d M Y H:i') }}
                </div>
            </div>
        @endforeach
    @else
        <div class="text-gray-500 text-sm italic">No signatures yet.</div>
    @endif
</div>
