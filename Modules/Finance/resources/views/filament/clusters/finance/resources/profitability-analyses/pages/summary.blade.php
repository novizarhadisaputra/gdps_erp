@if ($isPdf ?? false)
    <!DOCTYPE html>
    <html lang="en">
    @php
        $formatMoney = function ($amount) use ($record) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        };
    @endphp

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Profitability Analysis Summary</title>
        <style>
            /* Base PDF Reset & Styling for DomPDF */
            * {
                font-family: 'DejaVu Sans', sans-serif !important;
                box-sizing: border-box;
            }

            body {
                background: white;
                color: #1e293b;
                margin: 0;
                padding: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                table-layout: fixed;
            }

            th,
            td {
                border: 1px solid #e2e8f0;
                padding: 12px 16px;
                font-size: 11px;
                vertical-align: middle;
            }

            th {
                background-color: #f8fafc;
                color: #64748b;
                font-weight: bold;
                text-transform: uppercase;
            }

            /* Section Styling */
            .section-row {
                background-color: #f1f5f9;
                font-weight: 800;
                font-size: 12px;
            }

            .revenue-row {
                background-color: #eff6ff;
                color: #1e3a8a;
            }

            .cost-row {
                background-color: #f8fafc;
            }

            .profit-row {
                background-color: #ecfdf5;
                color: #065f46;
                font-weight: 900;
            }

            .net-profit-row {
                background-color: #0f172a;
                color: white;
                height: 80px;
            }

            /* Text Helpers */
            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .font-black {
                font-weight: 900;
            }

            .font-bold {
                font-weight: 700;
            }

            .italic {
                font-style: italic;
            }

            .uppercase {
                text-transform: uppercase;
            }

            /* Spacing & Sizes */
            .p-12 {
                padding: 40px;
            }

            .mb-8 {
                margin-bottom: 24px;
            }

            .mt-24 {
                margin-top: 80px;
            }

            /* Borders */
            .border-b {
                border-bottom: 1px solid #e2e8f0;
            }

            .border-b-2 {
                border-bottom: 2px solid #0f172a;
            }

            /* Custom Widths */
            .w-32 {
                width: 120px;
            }

            .w-48 {
                width: 180px;
            }
        </style>
    </head>

    <body style="background: white !important;">
    @else
        <x-filament-panels::page>
@endif

<div class="bg-white dark:bg-gray-950 shadow-xl rounded-sm border border-gray-200 dark:border-gray-800 printable mx-auto overflow-hidden w-full"
    style="{{ $isPdf ?? false ? 'border: none; box-shadow: none;' : 'min-height: 29.7cm;' }}">

    {{-- Top Branding Bar --}}
    <div class="h-1.5 bg-primary-600 w-full"></div>

    <div class="p-12">
        {{-- Header Section --}}
        <table style="width: 100%; border: none; margin-bottom: 20px; border-bottom: 2px solid #000;">
            <tr>
                <td style="border: none; text-align: left; vertical-align: top; padding: 0;">
                    <h2 style="font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; color: #000;">
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
                    <div style="font-family: monospace; font-size: 10px; color: #94a3b8; text-transform: uppercase;">
                        {{ $record->document_number }}</div>
                </td>
            </tr>
        </table>

        @if ($isPdf ?? false)
            <table style="width: 100%; border: none; margin-bottom: 30px;">
                <tr>
                    <td style="width: 50%; border: none; padding-right: 20px; vertical-align: top;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Customer</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right; font-weight: 900;">
                                    {{ $record->customer?->name }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Work Scheme</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right; font-weight: bold;">
                                    {{ $record->workScheme?->name }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Date Issued</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right;">
                                    {{ now()->format('d F Y') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; border: none; padding-left: 20px; vertical-align: top;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Project Ref</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right; font-weight: bold;">
                                    {{ $record->project_number ?? 'PENDING' }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Proposal No</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right; font-weight: bold;">
                                    {{ $record->proposal?->proposal_number ?? 'NOT ASSIGNED' }}</td>
                            </tr>
                            <tr>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                                    Currency</td>
                                <td
                                    style="border: none; border-bottom: 1px solid #f1f5f9; padding: 5px 0; font-size: 11px; text-align: right; font-weight: bold;">
                                    IDR</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        @else
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
        @endif

        {{-- Main Statement Table --}}
        <div class="border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700"
                        style="{{ $isPdf ?? false ? 'background-color: #f8fafc;' : '' }}">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] border-r border-slate-200 dark:border-slate-700"
                            style="{{ $isPdf ?? false ? 'color: #64748b; font-size: 9px; width: 60%;' : '' }}">
                            Particulars / Description</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-center border-r border-slate-200 dark:border-slate-700 w-32"
                            style="{{ $isPdf ?? false ? 'color: #64748b; font-size: 9px; width: 15%;' : '' }}">
                            Metrics</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-right w-48 font-mono"
                            style="{{ $isPdf ?? false ? 'color: #64748b; font-size: 9px; width: 25%;' : '' }}">
                            Amount (IDR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr class="bg-blue-50/40 dark:bg-blue-900/10 group"
                        style="{{ $isPdf ?? false ? 'background-color: #eff6ff;' : '' }}">
                        <td class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700"
                            style="{{ $isPdf ?? false ? 'font-weight: 800; color: #1e3a8a;' : '' }}">
                            <span class="text-blue-600 mr-2 font-black"
                                style="{{ $isPdf ?? false ? 'color: #2563eb;' : '' }}">I.</span> Total Revenue
                        </td>
                        <td class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400"
                            style="{{ $isPdf ?? false ? 'text-align: center; color: #94a3b8;' : '' }}">
                            -</td>
                        <td class="px-8 py-5 text-right font-black text-slate-950 dark:text-white tabular-nums text-lg"
                            style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 800; font-size: 14px;' : '' }}">
                            {{ $isPdf ?? false ? 'Rp ' . number_format($record->revenue_per_month, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
                            {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->revenue_per_month, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->revenue_per_month, 'IDR')@endunless
                        @endunless
                    </td>
                </tr>
                <tr>
                    <td
                        class="px-12 py-3 text-xs text-gray-500 italic border-r border-gray-200 dark:border-gray-700">
                        Base Project Fee / Price</td>
                    <td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
                    <td class="px-6 py-2 text-right text-xs text-gray-400 tabular-nums">
                        {{ $isPdf ?? false ? 'Rp ' . number_format($record->revenue_per_month, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
                        {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->revenue_per_month, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->revenue_per_month, 'IDR')@endunless
                    @endunless
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
                        {{ $isPdf ?? false ? 'Rp ' . number_format($record->management_fee, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
                        {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->management_fee, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->management_fee, 'IDR')@endunless
                    @endunless
                </td>
            </tr>
        @endif

        {{-- DIRECT COSTS section --}}
        <tr class="bg-slate-50/80 dark:bg-slate-900/40"
            style="{{ $isPdf ?? false ? 'background-color: #f1f5f9;' : '' }}">
            <td class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700"
                style="{{ $isPdf ?? false ? 'font-weight: 800;' : '' }}">
                <span class="text-slate-400 mr-2 font-black"
                    style="{{ $isPdf ?? false ? 'color: #94a3b8;' : '' }}">II.</span> Direct
                Operating Costs
            </td>
            <td class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400"
                style="{{ $isPdf ?? false ? 'text-align: center; color: #94a3b8;' : '' }}">
                -</td>
            <td class="px-8 py-5 text-right font-black text-slate-900 dark:text-slate-100 tabular-nums"
                style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 800;' : '' }}">
                ({{ $isPdf ?? false ? 'Rp ' . number_format($record->direct_cost, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
                {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->direct_cost, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->direct_cost, 'IDR')@endunless
            @endunless)</td>
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
            <td class="px-12 py-3 text-xs text-gray-600 border-r border-gray-200 dark:border-gray-700">
                {{ $category?->name ?? 'Miscellaneous' }}</td>
            <td
                class="px-8 py-3 text-center text-[9px] font-mono text-gray-400 border-r border-gray-200 dark:border-gray-700 uppercase">
                {{ $category?->name === 'Manpower' ? (int) $items->sum('quantity') . ' Headcount' : '-' }}
            </td>
            <td class="px-8 py-3 text-right text-xs text-gray-600 tabular-nums">
                {{ $isPdf ?? false ? 'Rp ' . number_format($items->sum('total_monthly_cost'), 0, ',', '.') : '' }}@unless ($isPdf ?? false)
                {{ ($isPdf ?? false) ? 'Rp ' . number_format($items->sum('total_monthly_cost'), 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($items->sum('total_monthly_cost'), 'IDR')@endunless
            @endunless
        </td>
    </tr>
@endforeach

{{-- GROSS PROFIT --}}
<tr class="bg-emerald-50/60 dark:bg-emerald-900/10 border-y-2 border-emerald-100 dark:border-emerald-900/30"
    style="{{ $isPdf ?? false ? 'background-color: #ecfdf5;' : '' }}">
    <td class="px-8 py-6 font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700"
        style="{{ $isPdf ?? false ? 'font-weight: 900; color: #065f46;' : '' }}">
        <span class="mr-2 font-black text-emerald-600"
            style="{{ $isPdf ?? false ? 'color: #059669;' : '' }}">III.</span> Gross Profit /
        Margin
    </td>
    <td class="px-8 py-6 text-center border-r border-slate-200 dark:border-slate-700"
        style="{{ $isPdf ?? false ? 'text-align: center;' : '' }}">
        <span
            class="bg-emerald-600 text-white px-3 py-1 text-[10px] font-black tabular-nums shadow-sm rounded-sm"
            style="{{ $isPdf ?? false ? 'background-color: #059669; color: white; padding: 2px 5px;' : '' }}">{{ number_format($record->margin_percentage, 2) }}%</span>
    </td>
    <td class="px-8 py-6 text-right font-black text-emerald-700 dark:text-emerald-400 text-xl tabular-nums"
        style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 900; color: #059669; font-size: 16px;' : '' }}">
        {{ $isPdf ?? false ? 'Rp ' . number_format($record->revenue_per_month - $record->direct_cost, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
        {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->revenue_per_month - $record->direct_cost, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->revenue_per_month - $record->direct_cost, 'IDR')@endunless
    @endunless
</td>
</tr>

{{-- INDIRECT COSTS --}}
<tr class="bg-slate-50/80 dark:bg-slate-900/40"
style="{{ $isPdf ?? false ? 'background-color: #f8fafc;' : '' }}">
<td class="px-8 py-5 font-black text-slate-900 dark:text-white uppercase tracking-tight border-r border-slate-200 dark:border-slate-700"
    style="{{ $isPdf ?? false ? 'font-weight: 800;' : '' }}">
    <span class="text-slate-400 mr-2 font-black"
        style="{{ $isPdf ?? false ? 'color: #94a3b8;' : '' }}">IV.</span> Indirect &
    Overhead Costs
</td>
<td class="px-8 py-5 text-center border-r border-slate-200 dark:border-slate-700 text-slate-400"
    style="{{ $isPdf ?? false ? 'text-align: center; color: #94a3b8;' : '' }}">
    -</td>
<td class="px-8 py-5 text-right font-black text-slate-800 dark:text-slate-200 tabular-nums"
    style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 800;' : '' }}">
    ({{ $isPdf ?? false ? 'Rp ' . number_format($record->items()->whereHas('category', fn($q) => $q->where('type', 'indirect'))->sum('total_monthly_cost'), 0, ',', '.') : '' }}@unless ($isPdf ?? false)
    {{ ($isPdf ?? false) ? 'Rp ' . number_format($record->items()->whereHas('category', fn($q) => $q->where('type', 'indirect'))->sum('total_monthly_cost'), 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->items()->whereHas('category', fn($q) => $q->where('type', 'indirect'))->sum('total_monthly_cost'), 'IDR')@endunless
@endunless)</td>
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
    {{ $isPdf ?? false ? 'Rp ' . number_format($item->total_monthly_cost, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
    {{ ($isPdf ?? false) ? 'Rp ' . number_format($item->total_monthly_cost, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($item->total_monthly_cost, 'IDR')@endunless
@endunless
</td>
</tr>
@endforeach

{{-- EBITDA --}}
<tr class="bg-amber-50/40 dark:bg-amber-900/10"
style="{{ $isPdf ?? false ? 'background-color: #fffbeb;' : '' }}">
<td class="px-8 py-5 font-black text-amber-900 dark:text-amber-400 uppercase tracking-tight border-r border-gray-200 dark:border-gray-700"
style="{{ $isPdf ?? false ? 'font-weight: 800; color: #92400e;' : '' }}">
<span class="text-amber-600 mr-2"
style="{{ $isPdf ?? false ? 'color: #d97706;' : '' }}">V.</span> EBITDA
</td>
<td class="px-8 py-5 text-center border-r border-gray-200 dark:border-gray-700"
style="{{ $isPdf ?? false ? 'text-align: center;' : '' }}">-</td>
<td class="px-8 py-5 text-right font-black text-amber-600 dark:text-amber-400 tabular-nums"
style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 800; color: #d97706;' : '' }}">
{{ $isPdf ?? false ? 'Rp ' . number_format($record->ebitda, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->ebitda, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->ebitda, 'IDR')@endunless
@endunless
</td>
</tr>
<tr>
<td
class="px-10 py-2 text-xs text-gray-400 italic border-r border-gray-200 dark:border-gray-700">
Depreciation & Amortization</td>
<td class="px-8 py-3 text-center border-r border-gray-200 dark:border-gray-700">-</td>
<td class="px-8 py-3 text-right text-xs text-gray-400 tabular-nums">
({{ $isPdf ?? false ? 'Rp ' . number_format($record->depreciation + $record->manual_depreciation, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->depreciation + $record->manual_depreciation, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->depreciation + $record->manual_depreciation, 'IDR')@endunless
@endunless)
</td>
</tr>

{{-- EBIT / EBT --}}
<tr class="bg-gray-50/30 dark:bg-gray-900/20"
style="{{ $isPdf ?? false ? 'background-color: #f9fafb;' : '' }}">
<td class="px-8 py-4 font-bold text-gray-600 dark:text-gray-400 uppercase text-xs border-r border-gray-200 dark:border-gray-700"
style="{{ $isPdf ?? false ? 'font-weight: 700; color: #4b5563;' : '' }}">
EBIT (Earnings Before Interest & Tax)</td>
<td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700"
style="{{ $isPdf ?? false ? 'text-align: center;' : '' }}">-</td>
<td class="px-8 py-4 text-right font-bold text-gray-700 dark:text-gray-300 tabular-nums"
style="{{ $isPdf ?? false ? 'text-align: right; font-weight: 700;' : '' }}">
{{ $isPdf ?? false ? 'Rp ' . number_format($record->ebit, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->ebit, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->ebit, 'IDR')@endunless
@endunless
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
({{ $isPdf ?? false ? 'Rp ' . number_format($record->ebit - $record->ebt, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->ebit - $record->ebt, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->ebit - $record->ebt, 'IDR')@endunless
@endunless)
</td>
</tr>
<tr class="bg-gray-50/30 dark:bg-gray-900/20 font-black"
style="{{ $isPdf ?? false ? 'background-color: #f9fafb; font-weight: 800;' : '' }}">
<td class="px-8 py-4 uppercase text-xs border-r border-gray-200 dark:border-gray-700">EBT
(Earnings Before Tax)</td>
<td class="px-8 py-4 text-center border-r border-gray-200 dark:border-gray-700"
style="{{ $isPdf ?? false ? 'text-align: center;' : '' }}">-</td>
<td class="px-8 py-4 text-right tabular-nums"
style="{{ $isPdf ?? false ? 'text-align: right;' : '' }}">{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->ebt, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->ebt, 'IDR')@endunless</td>
</tr>
<tr>
<td class="px-12 py-3 text-xs text-secondary-500 font-medium italic border-r border-slate-200 dark:border-slate-700 font-semibold"
style="{{ $isPdf ?? false ? 'color: #64748b !important;' : '' }}">
Corporate Income Tax (Est.)</td>
<td class="px-8 py-3 text-center text-[10px] text-slate-400 border-r border-slate-200 dark:border-slate-700 tabular-nums font-bold"
style="{{ $isPdf ?? false ? 'text-align: center;' : '' }}">
{{ number_format($record->tax_rate, 2) }}%</td>
<td class="px-8 py-3 text-right text-xs text-slate-500 tabular-nums"
style="{{ $isPdf ?? false ? 'text-align: right;' : '' }}">
({{ ($isPdf ?? false) ? 'Rp ' . number_format($record->ebt - $record->net_profit, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->ebt - $record->net_profit, 'IDR')@endunless)
</td>
</tr>

{{-- NET PROFIT FINAL --}}
<tr class="bg-slate-900 text-white dark:bg-white dark:text-slate-950 shadow-2xl {{ $isPdf ?? false ? 'net-profit-row' : '' }}"
style="{{ $isPdf ?? false ? 'background-color: #0f172a !important; color: white !important;' : '' }}">
<td class="px-8 py-10 font-black text-3xl uppercase tracking-[0.2em]"
style="{{ $isPdf ?? false ? 'font-size: 24px;' : '' }}">Net Profit</td>
<td class="px-8 py-10 text-center border-x border-slate-700 dark:border-slate-200"
style="{{ $isPdf ?? false ? 'border-left: 1px solid #334155; border-right: 1px solid #334155;' : '' }}">
<div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 leading-none"
style="{{ $isPdf ?? false ? 'color: #94a3b8;' : '' }}">
Profitability Ratio</div>
<div class="text-2xl font-black tabular-nums tracking-tighter shadow-sm"
style="{{ $isPdf ?? false ? 'font-size: 18px;' : '' }}">
{{ number_format($record->net_profit_margin, 2) }}%</div>
</td>
<td class="px-8 py-10 text-right text-4xl font-black tabular-nums shadow-inner"
style="{{ $isPdf ?? false ? 'font-size: 28px;' : '' }}">
{{ $isPdf ?? false ? 'Rp ' . number_format($record->net_profit, 0, ',', '.') : '' }}@unless ($isPdf ?? false)
{{ ($isPdf ?? false) ? 'Rp ' . number_format($record->net_profit, 0, ',', '.') : '' }}@unless($isPdf ?? false)@money($record->net_profit, 'IDR')@endunless
@endunless
</td>
</tr>
</tbody>
</table>
</div>

{{-- Signature Section --}}
@if ($isPdf ?? false)
<table style="width: 100%; border: none; margin-top: 60px;">
<tr>
@foreach (['Approver' => 'CEO', 'Reviewer I' => 'GH Business Support', 'Reviewer II' => 'GH Operation', 'Reviewer III' => 'GH Finance'] as $label => $title)
<td
style="width: 25%; border: none; text-align: center; vertical-align: bottom; padding: 0 10px;">
<p
style="font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 60px;">
{{ $label }}</p>
<div style="border-top: 1px solid #000; padding-top: 5px;">
<p
style="font-size: 10px; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1;">
{{ $title }}</p>
<p
style="font-size: 7px; color: #94a3b8; text-transform: uppercase; margin: 2px 0 0 0;">
Garuda Daya Pratama Sejahtera</p>
</div>
</td>
@endforeach
</tr>
</table>
@else
<div class="mt-24 grid grid-cols-4 gap-8">
@foreach (['Approver' => 'CEO', 'Reviewer I' => 'GH Business Support', 'Reviewer II' => 'GH Operation', 'Reviewer III' => 'GH Finance'] as $label => $title)
<div class="flex flex-col items-center">
<p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-16">
{{ $label }}</p>
<div class="w-full border-t border-gray-900 dark:border-white pt-2 text-center">
<p class="text-[10px] font-black text-gray-900 dark:text-white uppercase leading-tight">
{{ $title }}</p>
<p class="text-[8px] font-medium text-gray-400 uppercase tracking-tighter">Garuda Daya
Pratama Sejahtera</p>
</div>
</div>
@endforeach
</div>
@endif
</div>
</div>


<style>
@unless ($isPdf ?? false)
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700;800&display=swap');
@endunless

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
@if ($isPdf ?? false)
</body>

</html>
@else
</x-filament-panels::page>
@endif
