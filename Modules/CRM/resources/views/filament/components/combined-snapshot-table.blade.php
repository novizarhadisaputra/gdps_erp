<div class="p-0" style="font-family: 'Inter', sans-serif; color: #000;">

    <table class="w-full border-collapse border-y border-black text-[11px]">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black px-2 py-1.5 text-center w-8">No</th>
                <th class="border border-black px-2 py-1.5 text-left">Description / Job Position</th>
                <th class="border border-black px-2 py-1.5 text-center w-16">Unit</th>
                <th class="border border-black px-2 py-1.5 text-right w-20">Qty</th>
                <th class="border border-black px-2 py-1.5 text-right w-28">Unit Price</th>
                <th class="border border-black px-2 py-1.5 text-right w-32 font-bold">Total (IDR)</th>
            </tr>
        </thead>
        <tbody class="divide-y-0">
            @php $globalIndex = 1; @endphp
            @foreach($items as $row)
                <tr>
                    <td class="border border-black px-2 py-1.5 text-center">{{ $globalIndex++ }}</td>
                    <td class="border border-black px-2 py-1.5 font-medium">{{ $row['description'] ?? '-' }}</td>
                    <td class="border border-black px-2 py-1.5 text-center">{{ $row['uom'] ?? 'Unit' }}</td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">{{ number_format($row['quantity'] ?? 0) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right text-gray-600">
                        {{ number_format($row['unit_price'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">
                        {{ number_format($row['total_price'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            @foreach($manpower as $row)
                <tr>
                    <td class="border border-black px-2 py-1.5 text-center">{{ $globalIndex++ }}</td>
                    <td class="border border-black px-2 py-1.5 font-medium italic">{{ $row['job_position_name'] ?? '-' }}</td>
                    <td class="border border-black px-2 py-1.5 text-center">{{ $row['uom'] ?? 'Person' }}</td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">{{ number_format($row['quantity'] ?? 0) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right text-gray-600">
                        {{ number_format($row['unit_price'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">
                        @php
                            $price = (float)($row['total_price'] ?? $row['total_monthly_cost'] ?? 0);
                        @endphp
                        @if($price > 0)
                            {{ number_format($price, 0, ',', '.') }}
                        @else
                            <span class="italic text-gray-300">Personnel</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if(count($items) === 0 && count($manpower) === 0)
                <tr>
                    <td colspan="6" class="border border-black px-2 py-3 text-center text-gray-400 italic font-mono">-- No Records in Snapshot --</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            @php
                $totalItems = collect($items)->sum(fn($i) => (float)($i['total_price'] ?? 0));
                $totalMP = collect($manpower)->sum(fn($m) => (float)($m['total_price'] ?? $m['total_monthly_cost'] ?? 0));
                $subtotal = (float)$totalItems + (float)$totalMP;
                
                $taxRate = (float)($record->tax?->rate ?? $record->tax_percentage ?? 11);
                $numerator = (int)($record->tax?->base_rate_numerator ?? 1);
                $denominator = (int)($record->tax?->base_rate_denominator ?? 1);
                
                $dpp = floor($subtotal * ($numerator / $denominator));
                $taxAmount = floor($dpp * ($taxRate / 100));
                
                $taxLabel = "Tax (" . ($record->tax?->name ?? "PPN {$taxRate}%") . ")";
                if ($numerator !== $denominator) {
                    $taxLabel .= " - Adj. {$numerator}/{$denominator}";
                }
                
                $grandTotal = $subtotal + $taxAmount;
            @endphp
            <tr class="font-bold border-t border-black text-[10px]">
                <td colspan="5" class="border border-black px-2 py-1.5 text-right uppercase">Subtotal (Monthly)</td>
                <td class="border border-black px-2 py-1.5 text-right">IDR {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr class="font-bold text-[10px] text-gray-600">
                <td colspan="5" class="border border-black px-2 py-1.5 text-right uppercase">{{ $taxLabel }}</td>
                <td class="border border-black px-2 py-1.5 text-right">IDR {{ number_format($taxAmount, 0, ',', '.') }}</td>
            </tr>
            <tr class="bg-gray-100 font-bold border-t-2 border-black uppercase text-[10px]">
                <td colspan="5" class="border border-black px-2 py-2 text-right">Grand Total / Month (Inc. Tax)</td>
                <td class="border border-black px-2 py-2 text-right text-sm tracking-tighter text-success-600">
                    IDR {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
