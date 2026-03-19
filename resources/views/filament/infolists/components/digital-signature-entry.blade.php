<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>

        @php
            $signatures = $getSignatures();
            $service = app(\Modules\MasterData\Services\SignatureService::class);
            $record = $getRecord();
            $rules = $service->getRequiredApprovers($record);
            
            // Helper to resolve role name
            $getRoleName = function ($roleIdentifiers) {
                if (empty($roleIdentifiers)) {
                    return '...';
                }
                $ids = is_array($roleIdentifiers) ? $roleIdentifiers : [$roleIdentifiers];

                return \Spatie\Permission\Models\Role::where(function ($q) use ($ids) {
                    $uuids = collect($ids)
                        ->filter(fn($id) => \Illuminate\Support\Str::isUuid($id))
                        ->toArray();
                    $names = collect($ids)
                        ->filter(fn($id) => !\Illuminate\Support\Str::isUuid($id))
                        ->toArray();
                    if (!empty($uuids)) {
                        $q->orWhereIn('id', $uuids);
                    }
                    if (!empty($names)) {
                        $q->orWhereIn('name', $names);
                    }
                })
                    ->pluck('name')
                    ->implode(' / ') ?:
                    (is_array($roleIdentifiers) ? implode(' / ', $roleIdentifiers) : $roleIdentifiers);
            };

            $buildItem = function($user, $role, $type, $isSigned, $date, $userNameFallback = null) {
                return [
                    'is_signed' => $isSigned,
                    'user_name' => $user?->name ?? $userNameFallback ?? ($isSigned ? '-' : 'Waiting...'),
                    'role' => $role,
                    'type' => $type,
                    'user' => $user,
                    'date' => $date,
                ];
            };

            $stages = collect();

            // 1. Preparation
            $creator = $record->creator ?? $record->lead?->creator;
            if ($creator) {
                $stages->push([
                    'label' => 'Preparation',
                    'items' => collect([
                        $buildItem($creator, $creator->position ?? 'Creator', 'Preparation', true, $record->created_at)
                    ])
                ]);
            }

            // 2. Review
            $reviewItems = collect();
            $reviewerRules = $rules->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::Reviewer);
            foreach ($reviewerRules as $rule) {
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::Reviewer && $service->isEligibleApprover($rule, $s->user));
                $reviewItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ??
                        ($rule->approver_type === 'Role'
                            ? $getRoleName($rule->approver_role)
                            : 'Reviewer'),
                    'Reviewer',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }
            if ($reviewItems->isNotEmpty()) $stages->push(['label' => 'Review & Verification', 'items' => $reviewItems]);

            // 3. Margin Authorization
            $marginItems = collect();
            $marginRules = $rules->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::MarginApproval);
            foreach ($marginRules as $rule) {
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::MarginApproval && $service->isEligibleApprover($rule, $s->user));
                $marginItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ??
                        ($rule->approver_type === 'Role'
                            ? $getRoleName($rule->approver_role)
                            : 'Margin Approval'),
                    'MarginApproval',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }
            // Fallback for direct MarginApproval
            $marginSignature = $signatures->firstWhere('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::MarginApproval);
            if ($marginItems->isEmpty() && $marginSignature) {
                $marginItems->push($buildItem($marginSignature->user, $marginSignature->role, 'MarginApproval', true, $marginSignature->signed_at));
            }
            if ($marginItems->isNotEmpty()) $stages->push(['label' => 'Margin Authorization', 'items' => $marginItems]);

            // 4. Final Approval
            $approvalItems = collect();
            $approverRules = $rules->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::Approver);
            foreach ($approverRules as $rule) {
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::Approver && $service->isEligibleApprover($rule, $s->user));
                $approvalItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ??
                        ($rule->approver_type === 'Role'
                            ? $getRoleName($rule->approver_role)
                            : 'Approver'),
                    'Approver',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }
            if ($approvalItems->isNotEmpty()) $stages->push(['label' => 'Final Approval', 'items' => $approvalItems]);

            // 5. Acknowledgment
            $ackItems = collect();
            $ackRules = $rules->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::Acknowledger);
            foreach ($ackRules as $rule) {
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::Acknowledger && $service->isEligibleApprover($rule, $s->user));
                $ackItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ??
                        ($rule->approver_type === 'Role'
                            ? $getRoleName($rule->approver_role)
                            : 'Acknowledger'),
                    'Acknowledger',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }
            if ($ackItems->isNotEmpty()) $stages->push(['label' => 'Acknowledgment', 'items' => $ackItems]);
        @endphp

        <div class="space-y-10">
            @forelse ($stages as $stage)
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] whitespace-nowrap">
                            {{ $stage['label'] }}
                        </span>
                        <div class="h-px bg-gray-100 dark:bg-gray-800 w-full"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($stage['items'] as $item)
                            @php
                                $qrCodeSvg = null;
                                if ($item['is_signed'] && $item['user']) {
                                    try {
                                        $qrData = $service->createSignatureData($item['user'], $record, $item['type']);
                                        $qrCodeSvg = $service->generateQRCode($qrData);
                                    } catch (\Exception $e) { $qrCodeSvg = null; }
                                }
                            @endphp

                            <div class="relative group border {{ $item['is_signed'] ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm' : 'border-gray-100 dark:border-gray-800 border-dashed bg-gray-50/30 dark:bg-gray-900/40 opacity-80' }} rounded-2xl transition-all duration-300">
                                <div class="p-4">
                                    <div class="mb-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $item['is_signed'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' }}">
                                            {{ $item['role'] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div class="shrink-0 w-16 h-16 {{ $item['is_signed'] ? 'bg-white' : 'bg-gray-50' }} rounded-lg border border-gray-100 flex items-center justify-center p-0.5 shadow-inner">
                                            @if ($qrCodeSvg)
                                                <img src="{{ $qrCodeSvg }}" class="w-full h-full object-contain mix-blend-multiply opacity-90" />
                                            @else
                                                <x-heroicon-o-pencil-square class="w-6 h-6 text-gray-200" />
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold truncate {{ $item['is_signed'] ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                                                {{ $item['user_name'] }}
                                            </h4>
                                            @if ($item['is_signed'] && $item['date'])
                                                <div class="mt-1 flex items-center text-[10px] text-emerald-600 font-medium uppercase tracking-tight">
                                                    <x-heroicon-m-check-badge class="w-3 h-3 mr-1" />
                                                    <span>{{ $item['date']->format('d M Y') }}</span>
                                                </div>
                                            @else
                                                <div class="mt-1 flex items-center text-[10px] text-amber-500 font-medium uppercase tracking-tight italic">
                                                    <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                                    <span>Pending</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-2xl p-8 text-center bg-gray-50/50">
                    <x-heroicon-o-document-magnifying-glass class="w-8 h-8 text-gray-200 mx-auto mb-3" />
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">No Approval Rules Defined</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dynamic-component>
