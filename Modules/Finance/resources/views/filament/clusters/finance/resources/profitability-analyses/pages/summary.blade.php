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
                                @money($record->revenue_per_month, 'IDR')
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-gray-500 italic border-r border-gray-200 dark:border-gray-700">
                                Base Project Fee / Price</td>
                            <td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-6 py-2 text-right text-xs text-gray-400 tabular-nums">
                                @money($record->revenue_per_month - $record->management_fee, 'IDR')
                            </td>
                        </tr>
                        @if ($record->management_fee > 0)
                            <tr>
                                <td
                                    class="px-10 py-2 text-xs text-gray-600 font-medium border-r border-gray-200 dark:border-gray-700 italic">
                                    Management Fee</td>
                                <td
                                    class="px-6 py-2 text-center text-[10px] font-black text-primary-600 border-r border-gray-200 dark:border-gray-700 tabular-nums">
                                    {{ number_format($record->management_fee_rate, 2) }}%</td>
                                <td class="px-6 py-2 text-right text-xs text-gray-600 tabular-nums">
                                    @money($record->management_fee, 'IDR')
                                </td>
                            </tr>
                        @endif

                        {{-- DIRECT COSTS section --}}
                        <tr class="bg-slate-50/80 dark:bg-slate-900/40">
                            <td
                                class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 mr-2 font-black">II.</span> Direct
                                Operating Costs
                            </td>
                            <td
                                class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400">
                                -</td>
                            <td class="px-8 py-5 text-right font-black text-slate-900 dark:text-slate-100 tabular-nums">
                                (@money($record->direct_cost, 'IDR'))</td>
                        </tr>

                        @php
                            $directCosts = $record
                                ->items()
                                ->whereHas('category', fn($q) => $q->where('type', 'direct'))
                                ->with('category')
                                ->get()
                                ->groupBy('direct_cost_category_id');
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
                                    @money($items->sum('total_monthly_cost'), 'IDR')
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
                                    class="bg-emerald-600 text-white px-3 py-1 text-[10px] font-black tabular-nums shadow-sm rounded-sm">{{ number_format($record->margin_percentage, 2) }}%</span>
                            </td>
                            <td
                                class="px-8 py-6 text-right font-black text-emerald-700 dark:text-emerald-400 text-xl tabular-nums">
                                @money($record->revenue_per_month - $record->direct_cost, 'IDR')
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
                                (@money($record->items()->whereHas('category', fn($q) => $q->where('type', 'indirect'))->sum('total_monthly_cost'), 'IDR'))</td>
                        </tr>

                        @php
                            $indirectItems = $record
                                ->items()
                                ->whereHas('category', fn($q) => $q->where('type', 'indirect'))
                                ->with('category')
                                ->get();
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
                                    @money($item->total_monthly_cost, 'IDR')
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
                                @money($record->ebitda, 'IDR')
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-10 py-2 text-xs text-gray-400 italic border-r border-gray-200 dark:border-gray-700">
                                Depreciation & Amortization</td>
                            <td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-3 text-right text-xs text-gray-400 tabular-nums">
                                (@money($record->depreciation + $record->manual_depreciation, 'IDR'))
                            </td>
                        </tr>

                        {{-- EBIT / EBT --}}
                        <tr class="bg-gray-50/30 dark:bg-gray-900/20">
                            <td
                                class="px-8 py-4 font-bold text-gray-600 dark:text-gray-400 uppercase text-xs border-r border-gray-200 dark:border-gray-700">
                                EBIT (Earnings Before Interest & Tax)</td>
                            <td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-4 text-right font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                                @money($record->ebit, 'IDR')
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-gray-400 italic border-r border-gray-200 dark:border-gray-700">
                                Finance Cost / Project Interest</td>
                            <td
                                class="px-8 py-3 text-center text-[10px] font-black text-amber-600 border-r border-gray-200 dark:border-gray-700 tabular-nums">
                                {{ number_format($record->interest_rate, 2) }}%</td>
                            <td class="px-8 py-3 text-right text-xs text-gray-400 tabular-nums">
                                (@money($record->ebit - $record->ebt, 'IDR'))
                            </td>
                        </tr>
                        <tr class="bg-gray-50/30 dark:bg-gray-900/20 font-black">
                            <td class="px-8 py-4 uppercase text-xs border-r border-gray-200 dark:border-gray-700">
                                EBT
                                (Earnings Before Tax)</td>
                            <td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                            <td class="px-8 py-4 text-right tabular-nums">
                                @money($record->ebt, 'IDR')
                            </td>
                        </tr>
                        <tr>
                            <td
                                class="px-12 py-3 text-xs text-secondary-500 font-medium italic border-r border-slate-200 dark:border-slate-700 font-semibold">
                                Corporate Income Tax (Est.)</td>
                            <td
                                class="px-8 py-3 text-center text-[10px] text-slate-400 border-r border-slate-200 dark:border-slate-700 tabular-nums font-bold">
                                {{ number_format($record->tax_rate, 2) }}%</td>
                            <td class="px-8 py-3 text-right text-xs text-slate-500 tabular-nums">
                                (
                                @money($record->ebt - $record->net_profit, 'IDR')
                                )
                            </td>
                        </tr>

                        {{-- NET PROFIT FINAL --}}
                        <tr class="bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-2xl">
                            <td class="px-8 py-10 font-black text-3xl uppercase tracking-[0.2em]">
                                Net Profit</td>
                            <td class="px-8 py-10 text-center border-x border-slate-700 dark:border-slate-200">
                                <div
                                    class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 leading-none">
                                    Profitability Ratio</div>
                                <div class="text-2xl font-black tabular-nums tracking-tighter shadow-sm">
                                    {{ number_format($record->net_profit_margin, 2) }}%</div>
                            </td>
                            <td class="px-8 py-10 text-right text-4xl font-black tabular-nums shadow-inner">
                                @money($record->net_profit, 'IDR')
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Signature Section --}}
            @php
                $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
                $rules = $signatureService->getRequiredApprovers($record);
                $signatures = $record->signatures;
                $marginSignature = $signatures->firstWhere('signature_type', 'margin_approval');
                $otherSignatures = $signatures->where('signature_type', '!=', 'margin_approval');
                
                $totalCols = ($marginSignature ? 1 : 0) + $rules->count();
            @endphp

            @if ($totalCols > 0)
                <div class="mt-24 grid grid-cols-{{ min(4, $totalCols) }} gap-12">
                    {{-- Margin Approval Signature --}}
                    @if ($marginSignature)
                        <div class="flex flex-col items-center">
                            <p class="text-[9px] font-black text-primary-600 uppercase tracking-widest mb-4">
                                Margin Approval</p>

                            @php
                                $qrUrl = $signatureService->createSignatureData(
                                    $marginSignature->user,
                                    $record,
                                    'margin_approval',
                                );
                                $qrCode = $signatureService->generateQRCode($qrUrl);
                            @endphp
                            <div class="mb-4">
                                <img src="{{ $qrCode }}"
                                    class="w-20 h-20 opacity-80 mix-blend-multiply dark:invert"
                                    alt="Signature QR">
                            </div>
                            <div class="w-full border-t border-blue-600 dark:border-blue-400 pt-2 text-center">
                                <p
                                    class="text-[10px] font-black text-gray-900 dark:text-white uppercase leading-tight">
                                    {{ $marginSignature->user->name }}
                                </p>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-tighter">
                                    {{ $marginSignature->role }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Approval Rules Signatures --}}
                    @foreach ($rules as $rule)
                        @php
                            $matchingSignature = $otherSignatures->first(function ($sig) use ($rule, $signatureService) {
                                return $signatureService->isEligibleApprover($rule, $sig->user);
                            });
                        @endphp
                        <div class="flex flex-col items-center">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">
                                {{ $rule->signature_type ?: 'Final Approval' }}</p>

                            @if ($matchingSignature)
                                @php
                                    $qrUrl = $signatureService->createSignatureData(
                                        $matchingSignature->user,
                                        $record,
                                        $matchingSignature->signature_type ?? 'approved',
                                    );
                                    $qrCode = $signatureService->generateQRCode($qrUrl);
                                @endphp
                                <div class="mb-4">
                                    <img src="{{ $qrCode }}"
                                        class="w-20 h-20 opacity-80 mix-blend-multiply dark:invert"
                                        alt="Signature QR">
                                </div>
                                <div class="w-full border-t border-gray-900 dark:border-white pt-2 text-center">
                                    <p
                                        class="text-[10px] font-black text-gray-900 dark:text-white uppercase leading-tight">
                                        {{ $matchingSignature->user->name }}
                                    </p>
                                    <p class="text-[8px] font-medium text-gray-400 uppercase tracking-tighter">
                                        {{ $matchingSignature->role }}</p>
                                </div>
                            @else
                                <div class="h-20 mb-4 flex items-center justify-center">
                                    <div
                                        class="w-16 h-16 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-pencil class="w-6 h-6 text-gray-100 dark:text-gray-800" />
                                    </div>
                                </div>
                                <div class="w-full border-t border-gray-200 dark:border-gray-800 pt-2 text-center">
                                    <p
                                        class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase leading-tight">
                                        @if ($rule->approver_type === 'Role')
                                            {{ is_array($rule->approver_role) ? implode(' / ', $rule->approver_role) : $rule->approver_role }}
                                        @elseif($rule->approver_type === 'Position')
                                            {{ is_array($rule->approver_position) ? implode(' / ', $rule->approver_position) : $rule->approver_position }}
                                        @else
                                            {{ $rule->approver_type }}
                                        @endif
                                    </p>
                                    <p
                                        class="text-[7px] font-medium text-gray-300 dark:text-gray-700 uppercase tracking-tighter">
                                        Waiting for Signature
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="mt-24 p-6 border-2 border-dashed border-gray-100 dark:border-gray-900 rounded-sm text-center">
                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">No Approval Rules Defined
                        for
                        this Document</p>
                </div>
            @endif

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
