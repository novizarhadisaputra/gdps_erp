<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>

        @php
            $signatures = $getSignatures();
            $service = app(\Modules\MasterData\Services\SignatureService::class);
            $record = $getRecord();
            $rules = $record ? $service->getRequiredApprovers($record) : collect();
            
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
            $creator = $record ? ($record->creator ?? $record->lead?->creator ?? $record->user ?? $record->lead?->user) : null;
            if ($creator && $record) {
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
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::Approver && $service->isEligibleApprover($rule, $s->user, $record));
                
                $roleLabel = 'Approver';
                if ($rule->approver_type === 'Role') {
                    $roleLabel = $getRoleName($rule->approver_role);
                } elseif ($rule->approver_type === 'Relationship') {
                    $path = $rule->approver_role[0] ?? '';
                    if (str_contains($path, 'oprep')) $roleLabel = 'Project Manager';
                    elseif (str_contains($path, 'projectManager')) $roleLabel = 'Project Manager';
                    elseif (str_contains($path, 'creator')) $roleLabel = 'Initiator';
                    else $roleLabel = ucwords(str_replace(['.', '_'], ' ', $path));
                }

                $approvalItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ?? $roleLabel,
                    'Approver',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }

            // Fallback: If no approver rules are defined, but there are actual signatures of type Approver, display them.
            $actualApproverSignatures = $signatures->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::Approver);
            foreach ($actualApproverSignatures as $sig) {
                if (!$approvalItems->contains(fn($item) => $item['user']?->id === $sig->user_id)) {
                    $approvalItems->push($buildItem(
                        $sig->user,
                        $sig->role,
                        'Approver',
                        true,
                        $sig->signed_at
                    ));
                }
            }

            // For General Information: if there are no approver signatures yet, show a pending signature card for the creator
            if ($record instanceof \Modules\CRM\Models\GeneralInformation && $approvalItems->isEmpty()) {
                $creator = $record->user;
                if ($creator) {
                    $approvalItems->push($buildItem(
                        null,
                        'Account Manager',
                        'Approver',
                        false,
                        null,
                        $creator->name
                    ));
                }
            }

            if ($approvalItems->isNotEmpty()) $stages->push(['label' => 'Final Approval', 'items' => $approvalItems]);

            // 5. Acknowledgment
            $ackItems = collect();
            $ackRules = $rules->where('signature_type', \Modules\MasterData\Enums\ApprovalSignatureType::Acknowledger);
            foreach ($ackRules as $rule) {
                $sig = $signatures->first(fn($s) => $s->signature_type === \Modules\MasterData\Enums\ApprovalSignatureType::Acknowledger && $service->isEligibleApprover($rule, $s->user, $record));
                $ackItems->push($buildItem(
                    $sig?->user,
                    $sig?->role ?? ($rule->approver_type === 'Role' ? $getRoleName($rule->approver_role) : 'Acknowledger'),
                    'Acknowledger',
                    (bool)$sig,
                    $sig?->signed_at
                ));
            }
            if ($ackItems->isNotEmpty()) $stages->push(['label' => 'Acknowledgment', 'items' => $ackItems]);
        @endphp

        <div class="space-y-12">
            @forelse ($stages as $stage)
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.25em] whitespace-nowrap">
                            {{ $stage['label'] }}
                        </span>
                        <div class="h-px bg-gradient-to-r from-gray-100 via-gray-100 to-transparent dark:from-gray-800 dark:via-gray-800 w-full"></div>
                    </div>

                    <div class="flex flex-wrap gap-8 items-start">
                        @foreach ($stage['items'] as $item)
                            <div class="flex flex-col items-center group w-44">
                                <div @class([
                                    'relative flex items-center justify-center w-28 h-28 mb-4 transition-all duration-500 rounded-2xl shadow-sm border-2',
                                    'bg-white dark:bg-gray-900 border-success-200 shadow-success-100/50 scale-100' => $item['is_signed'],
                                    'bg-gray-50/50 dark:bg-gray-900/50 border-gray-100 border-dashed scale-95 opacity-60 group-hover:opacity-100 group-hover:scale-100' => !$item['is_signed'],
                                ])>
                                    @if ($item['is_signed'])
                                        @php
                                            $qrCode = null;
                                            if ($item['user']) {
                                                $qrUrl = $service->createSignatureData($item['user'], $record, $item['type']);
                                                $qrCode = $service->generateQRCode($qrUrl);
                                            } elseif ($item['type'] === 'Customer') {
                                                // For customer, just show a checkmark or placeholder if we don't have a user
                                                $qrCode = null;
                                            }
                                        @endphp
                                        
                                        @if($qrCode)
                                            <img src="{{ $qrCode }}" alt="Signature" class="w-20 h-20 opacity-90 group-hover:opacity-100 transition-opacity mix-blend-multiply dark:mix-blend-normal">
                                        @else
                                            <div class="flex items-center justify-center w-20 h-20 text-success-500">
                                                <x-filament::icon icon="heroicon-o-check-badge" class="w-12 h-12" />
                                            </div>
                                        @endif

                                        <div class="absolute -bottom-2 -right-2 bg-success-500 text-white rounded-full p-1.5 shadow-lg border-2 border-white dark:border-gray-900">
                                            <x-filament::icon icon="heroicon-m-check-badge" class="w-4 h-4" />
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-gray-300">
                                            <x-filament::icon icon="heroicon-o-pencil-square" class="w-10 h-10 mb-1 opacity-20 group-hover:opacity-40 transition-opacity" />
                                            <span class="text-[10px] font-bold tracking-widest uppercase opacity-40">Pending</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="text-center w-full px-2">
                                    <div @class([
                                        'text-sm font-bold truncate leading-tight transition-colors duration-300',
                                        'text-gray-900 dark:text-white' => $item['is_signed'],
                                        'text-gray-400' => !$item['is_signed'],
                                    ]) title="{{ $item['user_name'] }}">
                                        {{ $item['user_name'] }}
                                    </div>
                                    <div @class([
                                        'text-[10px] font-semibold uppercase tracking-widest mt-1.5',
                                        'text-primary-600 dark:text-primary-400' => $item['is_signed'],
                                        'text-gray-400' => !$item['is_signed'],
                                    ])>
                                        {{ $item['role'] }}
                                    </div>
                                    @if ($item['date'])
                                        <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-medium bg-gray-100/80 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl bg-gray-50/30">
                    <div class="w-16 h-16 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 flex items-center justify-center mb-4">
                        <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-8 h-8 text-gray-200" />
                    </div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">No signature workflow defined</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dynamic-component>
