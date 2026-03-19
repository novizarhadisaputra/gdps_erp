@use(Modules\MasterData\Services\SignatureService)
@use(Spatie\Permission\Models\Role)
@use(Illuminate\Support\Str)
@use(Modules\MasterData\Enums\ApprovalSignatureType)

<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-950 shadow-xl rounded-sm border border-gray-200 dark:border-gray-800 printable mx-auto overflow-hidden w-full"
        style="min-height: 29.7cm;">

        {{-- Top Branding Bar --}}
        <div class="h-1.5 bg-primary-600 w-full"></div>

        <div class="p-12">
            {{-- Header Section --}}
            <table style="width: 100%; border: none; margin-bottom: 20px; border-bottom: 2px solid #000;">
                <tr>
                    <td style="border: none; text-align: left; vertical-align: top; padding: 0;">
                        <h2
                            style="font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; color: #000;">
                            Profitability Analysis</h2>
                        <p
                            style="font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin: 5px 0 0 0;">
                            Financial Summary Report</p>
                    </td>
                    <td style="border: none; text-align: right; vertical-align: top; padding: 0;">
                        <div
                            style="background-color: #000; color: #fff; padding: 4px 12px; display: inline-block; margin-bottom: 5px; font-weight: 900; font-size: 10px; text-transform: uppercase;">
                            {{ $record->status->getLabel() }}
                        </div>
                        <div
                            style="font-family: monospace; font-size: 10px; color: #94a3b8; text-transform: uppercase;">
                            {{ $record->document_number }}</div>
                    </td>
                </tr>
            </table>

            <div class="grid grid-cols-2 gap-x-16 mb-12">
                <div class="space-y-2">
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Customer</span>
                        <span
                            class="text-xs font-black text-gray-900 dark:text-white uppercase">{{ $record->customer?->name }}</span>
                    </div>
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Work
                            Scheme</span>
                        <span
                            class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $record->workScheme?->name }}</span>
                    </div>
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Date
                            Issued</span>
                        <span
                            class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ now()->format('d F Y') }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Project
                            Ref</span>
                        <span
                            class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $record->project_number ?? 'PENDING' }}</span>
                    </div>
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Proposal
                            No</span>
                        <span
                            class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $record->proposal?->proposal_number ?? 'NOT ASSIGNED' }}</span>
                    </div>
                    <div class="flex justify-between items-baseline border-b border-gray-100 dark:border-gray-800 pb-1">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Currency</span>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">IDR (Indonesian
                            Rupiah)</span>
                    </div>
                </div>
            </div>

            {{-- Main Statement Table --}}
            <div class="border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                            <th
                                class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] border-r border-slate-200 dark:border-slate-700">
                                Particulars / Description</th>
                            <th
                                class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-center border-r border-slate-200 dark:border-slate-700 w-32">
                                Metrics</th>
                            <th
                                class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-right w-48 font-mono">
                                Amount (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr class="bg-blue-50/40 dark:bg-blue-900/10 group">
                            <td
                                class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700">
                                <span class="text-blue-600 mr-2 font-black">I.</span> Total Revenue <span
                                    class="text-[10px] lowercase font-medium text-slate-400">(Excl. PPN)</span>
                            </td>
                            <td
                                class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400">
                                -</td>
                            <td
                                class="px-8 py-5 text-right font-black text-slate-950 dark:text-white tabular-nums text-lg">
                                @money($record->revenue_per_month, 'IDR', true)
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-gray-500 italic border-r border-gray-200 dark:border-gray-700">
                                Base Project Fee / Price</td>
                            <td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-6 py-2 text-right text-xs text-gray-400 tabular-nums">
                                @money($record->revenue_per_month - $record->management_fee, 'IDR', true)
                            </td>
                        </tr>
                        @if ($record->management_fee > 0)
                            <tr>
                                <td
                                    class="px-10 py-2 text-xs text-gray-600 font-medium border-r border-gray-200 dark:border-gray-700 italic">
                                    Management Fee</td>
                                <td
                                    class="px-6 py-2 text-center text-[10px] font-black text-primary-600 border-r border-gray-200 dark:border-gray-700 tabular-nums">
                                    {{ number_format($record->management_fee_rate, 2, ',', '.') }}%</td>
                                <td class="px-6 py-2 text-right text-xs text-gray-600 tabular-nums">
                                    @money($record->management_fee, 'IDR', true)
                                </td>
                            </tr>
                        @endif

                        {{-- DIRECT COSTS section --}}
                        <tr class="bg-slate-50/80 dark:bg-slate-900/40">
                            <td
                                class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 mr-2 font-black">II.</span> Direct Operating Costs
                            </td>
                            <td
                                class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400">
                                -</td>
                            <td class="px-8 py-5 text-right font-black text-slate-900 dark:text-slate-100 tabular-nums">
                                @money($record->direct_cost, 'IDR', true)
                            </td>
                        </tr>

                        @php
                            $directCosts = $record->getDirectItems()->groupBy('direct_cost_category_id');
                        @endphp

                        @foreach ($directCosts as $categoryId => $items)
                            @php $category = $items->first()->category; @endphp
                            <tr>
                                <td
                                    class="px-12 py-3 text-xs text-gray-600 border-r border-gray-200 dark:border-gray-700">
                                    {{ $category?->name ?? 'Miscellaneous' }}</td>
                                <td
                                    class="px-8 py-3 text-center text-[9px] font-mono text-gray-400 border-r border-gray-200 dark:border-gray-700 uppercase">
                                    {{ $category?->name === 'Manpower' ? (int) $items->sum('quantity') . ' Headcount' : '-' }}
                                </td>
                                <td class="px-8 py-3 text-right text-xs text-gray-600 tabular-nums">
                                    @money($items->sum('total_monthly_cost'), 'IDR', true)
                                </td>
                            </tr>
                        @endforeach

                        {{-- GROSS PROFIT --}}
                        <tr
                            class="bg-emerald-50/60 dark:bg-emerald-900/10 border-y-2 border-emerald-100 dark:border-emerald-900/30">
                            <td
                                class="px-8 py-6 font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700">
                                <span class="mr-2 font-black text-emerald-600">III.</span> Gross
                                Profit /
                                Margin
                            </td>
                            <td class="px-8 py-6 text-center border-r border-slate-200 dark:border-slate-700">
                                <span
                                    class="bg-emerald-600 text-white px-3 py-1 text-[10px] font-black tabular-nums shadow-sm rounded-sm">{{ number_format($record->margin_percentage, 2, ',', '.') }}%</span>
                            </td>
                            <td
                                class="px-8 py-6 text-right font-black text-emerald-700 dark:text-emerald-400 text-xl tabular-nums">
                                @money($record->revenue_per_month - $record->direct_cost, 'IDR', true)
                            </td>
                        </tr>

                        {{-- INDIRECT COSTS --}}
                        <tr class="bg-slate-50/80 dark:bg-slate-900/40">
                            <td
                                class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 mr-2 font-black">IV.</span> Indirect &
                                Overhead Costs
                            </td>
                            <td
                                class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400">
                                -</td>
                            <td class="px-8 py-5 text-right font-black text-slate-800 dark:text-slate-200 tabular-nums">
                                @money($record->getIndirectItems()->sum(fn($i) => (float) ($i->total_monthly_cost ?? 0)), 'IDR', true)</td>
                        </tr>

                        @php
                            $indirectItems = $record->getIndirectItems();
                        @endphp

                        @foreach ($indirectItems as $item)
                            <tr>
                                <td
                                    class="px-12 py-3 text-xs text-gray-500 italic border-r border-gray-200 dark:border-gray-700">
                                    {{ $item->category?->name ?? 'Indirect' }}</td>
                                <td
                                    class="px-8 py-3 text-center text-[9px] font-mono text-gray-300 border-r border-gray-200 dark:border-gray-700 tabular-nums">
                                    {{ $item->markup_percentage > 0 ? number_format($item->markup_percentage, 2) . '%' : '-' }}
                                </td>
                                <td class="px-8 py-3 text-right text-xs text-gray-500 tabular-nums">
                                    @money($item->total_monthly_cost, 'IDR', true)
                                </td>
                            </tr>
                        @endforeach

                        {{-- EBITDA --}}
                        <tr class="bg-amber-50/40 dark:bg-amber-900/10">
                            <td
                                class="px-8 py-5 font-black text-amber-900 dark:text-amber-400 uppercase tracking-tight border-r border-gray-200 dark:border-gray-700">
                                <span class="text-amber-600 mr-2">V.</span> EBITDA
                            </td>
                            <td class="px-8 py-5 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-5 text-right font-black text-amber-600 dark:text-amber-400 tabular-nums">
                                @money($record->ebitda, 'IDR', true)
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-10 py-2 text-xs text-gray-400 italic border-r border-gray-200 dark:border-gray-700">
                                Depreciation & Amortization</td>
                            <td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-3 text-right text-xs text-gray-400 tabular-nums">
                                (@money($record->depreciation + $record->manual_depreciation, 'IDR', true))
                            </td>
                        </tr>

                        {{-- EBIT / EBT --}}
                        <tr class="bg-gray-50/30 dark:bg-gray-900/20">
                            <td
                                class="px-8 py-4 font-bold text-gray-600 dark:text-gray-400 uppercase text-xs border-r border-gray-200 dark:border-gray-700">
                                EBIT (Earnings Before Interest & Tax)</td>
                            <td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-4 text-right font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                                @money($record->ebit, 'IDR', true)
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-gray-400 italic border-r border-gray-200 dark:border-gray-700">
                                Finance Cost / Project Interest</td>
                            <td
                                class="px-8 py-3 text-center text-[10px] font-black text-amber-600 border-r border-gray-200 dark:border-gray-700 tabular-nums">
                                {{ number_format($record->interest_rate, 2, ',', '.') }}%</td>
                            <td class="px-8 py-3 text-right text-xs text-gray-400 tabular-nums">
                                (@money($record->ebit - $record->ebt, 'IDR', true))
                            </td>
                        </tr>
                        <tr class="bg-gray-50/30 dark:bg-gray-900/20 font-black">
                            <td class="px-8 py-4 uppercase text-xs border-r border-gray-200 dark:border-gray-700">
                                EBT
                                (Earnings Before Tax)</td>
                            <td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-4 text-right tabular-nums">
                                @money($record->ebt, 'IDR', true)
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-secondary-500 font-medium italic border-r border-slate-200 dark:border-slate-700 font-semibold">
                                Corporate Income Tax (Est.)</td>
                            <td
                                class="px-8 py-3 text-center text-[10px] text-slate-400 border-r border-slate-200 dark:border-slate-700 tabular-nums font-bold">
                                {{ number_format($record->tax_rate, 2, ',', '.') }}%</td>
                            <td class="px-8 py-3 text-right text-xs text-slate-500 tabular-nums">
                                (
                                @money($record->ebt - $record->net_profit, 'IDR', true)
                                )
                            </td>
                        </tr>

                        {{-- NET PROFIT FINAL --}}
                        <tr class="bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-2xl">
                            <td class="px-8 py-6 font-black text-3xl uppercase tracking-[0.2em]">
                                Net Profit</td>
                            <td class="px-8 py-6 text-center border-x border-slate-700 dark:border-slate-200">
                                <div
                                    class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 leading-none">
                                    Profitability Ratio</div>
                                <div class="text-2xl font-black tabular-nums tracking-tighter shadow-sm">
                                    {{ number_format($record->net_profit_margin, 2, ',', '.') }}%</div>
                            </td>
                            <td class="px-8 py-6 text-right text-4xl font-black tabular-nums shadow-inner">
                                @money($record->net_profit, 'IDR', true)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Grouped Signature Section --}}
            @php
                $signatureService = app(SignatureService::class);
                $rules = $signatureService->getRequiredApprovers($record);
                $signatures = $record->signatures;

                // Helper to resolve role name
                $getRoleName = function ($roleIdentifiers) {
                    if (empty($roleIdentifiers)) {
                        return '...';
                    }
                    $ids = is_array($roleIdentifiers) ? $roleIdentifiers : [$roleIdentifiers];

                    return Role::where(function ($q) use ($ids) {
                        $uuids = collect($ids)
                            ->filter(fn($id) => Str::isUuid($id))
                            ->toArray();
                        $names = collect($ids)
                            ->filter(fn($id) => !Str::isUuid($id))
                            ->toArray();
                        if (!empty($uuids)) {
                            $q->orWhereIn('id', $uuids);
                        }
                        if (!empty($names)) {
                            $q->orWhereIn('name', $names);
                        }
                    })->pluck('name')
                        ->implode(' / ') ?:
                        (is_array($roleIdentifiers) ? implode(' / ', $roleIdentifiers) : $roleIdentifiers);
                };

                // Helper to resolve eligible user names
                $getEligibleUserNames = function ($rule) use ($signatureService) {
                    if (!$rule) {
                        return null;
                    }
                    $users = $signatureService->getEligibleUsers($rule);
                    if ($users->isEmpty()) {
                        return null;
                    }
                    return $users->pluck('name')->implode(', ');
                };

                // Helper to build display items
                $buildItem = function ($user, $role, $type, $isSigned, $date, $eligibleNames = null) {
                    return [
                        'is_signed' => $isSigned,
                        'signer_name' => $user?->name ?? ($isSigned ? '-' : ($eligibleNames ?: 'Waiting...')),
                        'role' => $role,
                        'type' => $type,
                        'user' => $user,
                        'date' => $date,
                    ];
                };

                $stages = collect();

                // 2. Review Stage
                $reviewItems = collect();
                $reviewerRules = $rules->where(
                    'signature_type',
                    ApprovalSignatureType::Reviewer,
                );
                foreach ($reviewerRules as $rule) {
                    $sig = $signatures->first(
                        fn($s) => $s->signature_type === 'Reviewer' &&
                            $signatureService->isEligibleApprover($rule, $s->user),
                    );
                    $reviewItems->push(
                        $buildItem(
                            $sig?->user,
                            $sig?->role ?? $getRoleName($rule->approver_role),
                            'Reviewer',
                            (bool) $sig,
                            $sig?->signed_at,
                            $getEligibleUserNames($rule)
                        ),
                    );
                }
                if ($reviewItems->isNotEmpty()) {
                    $stages->push(['label' => 'Review & Verification', 'items' => $reviewItems]);
                }

                // 3. Margin Authorization Stage
                $marginItems = collect();
                $marginSignature = $signatures->firstWhere('signature_type', 'MarginApproval');
                $marginRules = $rules->where(
                    'signature_type',
                    ApprovalSignatureType::MarginApproval,
                );

                foreach ($marginRules as $rule) {
                    $sig = $signatures->first(
                        fn($s) => $s->signature_type === 'MarginApproval' &&
                            $signatureService->isEligibleApprover($rule, $s->user),
                    );
                    $marginItems->push(
                        $buildItem(
                            $sig?->user,
                            $sig?->role ?? $getRoleName($rule->approver_role),
                            'MarginApproval',
                            (bool) $sig,
                            $sig?->signed_at,
                            $getEligibleUserNames($rule)
                        ),
                    );
                }
                // Fallback for direct MarginApproval without rule if any
                if ($marginItems->isEmpty() && $marginSignature) {
                    $marginItems->push(
                        $buildItem(
                            $marginSignature->user,
                            $marginSignature->role,
                            'MarginApproval',
                            true,
                            $marginSignature->signed_at,
                        ),
                    );
                }
                if ($marginItems->isNotEmpty()) {
                    $stages->push(['label' => 'Margin Authorization', 'items' => $marginItems]);
                }

                // 4. Final Approval Stage
                $approvalItems = collect();
                $approverRules = $rules->where(
                    'signature_type',
                    ApprovalSignatureType::Approver,
                );
                foreach ($approverRules as $rule) {
                    $sig = $signatures->first(
                        fn($s) => $s->signature_type === 'Approver' &&
                            $signatureService->isEligibleApprover($rule, $s->user),
                    );
                    $approvalItems->push(
                        $buildItem(
                            $sig?->user,
                            $sig?->role ?? $getRoleName($rule->approver_role),
                            'Approver',
                            (bool) $sig,
                            $sig?->signed_at,
                            $getEligibleUserNames($rule)
                        ),
                    );
                }
                if ($approvalItems->isNotEmpty()) {
                    $stages->push(['label' => 'Final Approval', 'items' => $approvalItems]);
                }

                // 5. Acknowledgment Stage
                $ackItems = collect();
                $ackRules = $rules->where(
                    'signature_type',
                    ApprovalSignatureType::Acknowledger,
                );
                foreach ($ackRules as $rule) {
                    $sig = $signatures->first(
                        fn($s) => $s->signature_type === 'Acknowledger' &&
                            $signatureService->isEligibleApprover($rule, $s->user),
                    );
                    $ackItems->push(
                        $buildItem(
                            $sig?->user,
                            $sig?->role ??
                                ($rule->approver_type === 'Role'
                                    ? $getRoleName($rule->approver_role)
                                    : 'Acknowledger'),
                            'Acknowledger',
                            (bool) $sig,
                            $sig?->signed_at,
                            $getEligibleUserNames($rule)
                        ),
                    );
                }
                if ($ackItems->isNotEmpty()) {
                    $stages->push(['label' => 'Acknowledgment', 'items' => $ackItems]);
                }
            @endphp

            <div class="mt-16 space-y-12">
                @foreach ($stages as $stage)
                    <div
                        class="bg-gray-50/50 dark:bg-gray-900/20 p-6 rounded-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-4 mb-6">
                            <h3
                                class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] whitespace-nowrap">
                                {{ $stage['label'] }}</h3>
                            <div class="h-px bg-slate-200 dark:bg-slate-800 w-full"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-8">
                            @foreach ($stage['items'] as $item)
                                <div class="flex flex-col items-center">
                                    {{-- Role Title --}}
                                    <div class="mb-4 text-center h-8 flex flex-col justify-end">
                                        <span
                                            class="text-[8px] font-black {{ $item['is_signed'] ? 'text-primary-600' : 'text-slate-300' }} uppercase tracking-widest leading-tight">
                                            {{ $item['role'] }}
                                        </span>
                                    </div>

                                    {{-- QR Code / Placeholder --}}
                                    <div class="relative mb-4">
                                        @if ($item['is_signed'] && $item['user'])
                                            @php
                                                $qrUrl = $signatureService->createSignatureData(
                                                    $item['user'],
                                                    $record,
                                                    $item['type'],
                                                );
                                                $qrCode = $signatureService->generateQRCode($qrUrl);
                                            @endphp
                                            <div
                                                class="p-1 bg-white border border-slate-100 rounded-sm shadow-sm transition-transform hover:scale-105">
                                                <img src="{{ $qrCode }}"
                                                    class="w-16 h-16 mix-blend-multiply opacity-90"
                                                    alt="Signature QR">
                                            </div>
                                            <div
                                                class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full p-0.5 border-2 border-white shadow-sm">
                                                <x-heroicon-m-check-badge class="w-3 h-3" />
                                            </div>
                                        @else
                                            <div
                                                class="w-16 h-16 border-2 border-dashed border-slate-200 rounded-sm flex items-center justify-center bg-white/50">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 text-slate-200" />
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Signer Name & Date --}}
                                    <div
                                        class="w-full text-center border-t border-slate-200 dark:border-slate-800 pt-3">
                                        <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase truncate px-1"
                                            title="{{ $item['signer_name'] }}">
                                            {{ $item['signer_name'] }}
                                        </p>
                                        @if ($item['is_signed'] && $item['date'])
                                            <p class="text-[7px] font-mono text-slate-400 mt-1 uppercase">
                                                {{ $item['date']->format('d M Y') }}
                                            </p>
                                        @else
                                            <p
                                                class="text-[7px] font-semibold text-slate-300 mt-1 uppercase tracking-tighter italic">
                                                Pending
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700;800&display=swap');

        .printable {
            font-family: 'Inter', sans-serif !important;
        }

        .tabular-nums {
            font-family: 'JetBrains Mono', monospace !important;
            font-variant-numeric: tabular-nums;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .printable {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            body {
                background: white !important;
            }

            /* Force colors in print */
            .bg-gray-900 {
                background-color: #111827 !important;
                -webkit-print-color-adjust: exact;
            }

            .text-white {
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
            }

            .bg-primary-600 {
                background-color: #2563eb !important;
                -webkit-print-color-adjust: exact;
            }

            .bg-emerald-600 {
                background-color: #059669 !important;
                -webkit-print-color-adjust: exact;
            }

            .bg-primary-50\/20 {
                background-color: #eff6ff !important;
                -webkit-print-color-adjust: exact;
            }

            .bg-emerald-50\/50 {
                background-color: #ecfdf5 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</x-filament-panels::page>
