@php
    $formatMoney = function ($amount) {
        return number_format($amount, 0, ',', '.');
    };
@endphp
<table>
    <thead>
        <tr>
            <th colspan="3" style="font-size: 16pt; font-weight: bold; text-align: center;">PROFITABILITY ANALYSIS</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">{{ $record->document_number }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Customer:</th>
            <th colspan="2">{{ $record->customer?->name }}</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Work Scheme:</th>
            <th colspan="2">{{ $record->workScheme?->name }}</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Project Ref:</th>
            <th colspan="2">{{ $record->project_number ?? 'PENDING' }}</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Status:</th>
            <th colspan="2">{{ $record->status->getLabel() }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        <tr style="background-color: #f1f5f9;">
            <th style="font-weight: bold; border: 1px solid #000; width: 50%;">Particulars / Description</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; width: 15%;">Metrics</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right; width: 35%;">Amount (IDR)</th>
        </tr>
    </thead>
    <tbody>
        {{-- REVENUE --}}
        <tr style="background-color: #eff6ff; font-weight: bold;">
            <td style="border: 1px solid #000;">I. Total Revenue</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($record->revenue_per_month) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding-left: 20px;">- Base Project Fee / Price</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ $formatMoney($record->revenue_per_month - $record->management_fee) }}</td>
        </tr>
        @if ($record->management_fee > 0)
            <tr>
                <td style="border: 1px solid #000; padding-left: 20px;">- Management Fee</td>
                <td style="border: 1px solid #000; text-align: center;">
                    {{ number_format($record->management_fee_rate, 2) }}%</td>
                <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($record->management_fee) }}</td>
            </tr>
        @endif

        {{-- DIRECT COSTS --}}
        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td style="border: 1px solid #000;">II. Direct Operating Costs</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">({{ $formatMoney($record->direct_cost) }})</td>
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
                <td style="border: 1px solid #000; padding-left: 20px;">- {{ $category?->name ?? 'Miscellaneous' }}
                </td>
                <td style="border: 1px solid #000; text-align: center;">
                    {{ $category?->name === 'Manpower' ? (int) $items->sum('quantity') . ' HC' : '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: right;">
                    {{ $formatMoney($items->sum('total_monthly_cost')) }}</td>
            </tr>
        @endforeach

        {{-- GROSS PROFIT --}}
        <tr style="background-color: #ecfdf5; font-weight: bold;">
            <td style="border: 1px solid #000;">III. Gross Profit / Margin</td>
            <td style="border: 1px solid #000; text-align: center;">{{ number_format($record->margin_percentage, 2) }}%
            </td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ $formatMoney($record->revenue_per_month - $record->direct_cost) }}</td>
        </tr>

        {{-- INDIRECT COSTS --}}
        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td style="border: 1px solid #000;">IV. Indirect & Overhead Costs</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">
                ({{ $formatMoney($record->items()->whereHas('category', fn($q) => $q->where('type', 'indirect'))->sum('total_monthly_cost')) }})
            </td>
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
                <td style="border: 1px solid #000; padding-left: 20px;">- {{ $item->category?->name ?? 'Indirect' }}
                </td>
                <td style="border: 1px solid #000; text-align: center;">
                    {{ $item->markup_percentage > 0 ? number_format($item->markup_percentage, 2) . '%' : '-' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($item->total_monthly_cost) }}
                </td>
            </tr>
        @endforeach

        {{-- EBITDA --}}
        <tr style="background-color: #fffbeb; font-weight: bold;">
            <td style="border: 1px solid #000;">V. EBITDA</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($record->ebitda) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding-left: 20px;">- Depreciation & Amortization</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">
                ({{ $formatMoney($record->depreciation + $record->manual_depreciation) }})</td>
        </tr>

        {{-- EBIT --}}
        <tr style="background-color: #f9fafb; font-weight: bold;">
            <td style="border: 1px solid #000;">VI. EBIT (Earnings Before Interest & Tax)</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($record->ebit) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding-left: 20px;">- Finance Cost / Project Interest</td>
            <td style="border: 1px solid #000; text-align: center;">{{ number_format($record->interest_rate, 2) }}%
            </td>
            <td style="border: 1px solid #000; text-align: right;">({{ $formatMoney($record->ebit - $record->ebt) }})
            </td>
        </tr>

        {{-- EBT --}}
        <tr style="background-color: #f9fafb; font-weight: bold;">
            <td style="border: 1px solid #000;">VII. EBT (Earnings Before Tax)</td>
            <td style="border: 1px solid #000; text-align: center;">-</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $formatMoney($record->ebt) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding-left: 20px;">- Corporate Income Tax (Est.)</td>
            <td style="border: 1px solid #000; text-align: center;">{{ number_format($record->tax_rate, 2) }}%</td>
            <td style="border: 1px solid #000; text-align: right;">
                ({{ $formatMoney($record->ebt - $record->net_profit) }})</td>
        </tr>

        {{-- NET PROFIT --}}
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td style="border: 1px solid #000; font-size: 14pt;">NET PROFIT</td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ number_format($record->net_profit_margin, 2) }}%
            </td>
            <td style="border: 1px solid #000; text-align: right; font-size: 14pt;">
                {{ $formatMoney($record->net_profit) }}</td>
        </tr>
    </tbody>
</table>
