<div class="p-0" style="font-family: 'Inter', sans-serif; color: #000;">

    <table class="w-full border-collapse border-y border-black text-[11px]">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black px-2 py-1.5 text-center w-8">No</th>
                <th class="border border-black px-2 py-1.5 text-left">Description / Job Position</th>
                <th class="border border-black px-2 py-1.5 text-center w-16">Unit</th>
                <th class="border border-black px-2 py-1.5 text-right w-24">Qty</th>
                <th class="border border-black px-2 py-1.5 text-right w-32 font-bold">Amount (IDR)</th>
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
                    <td class="border border-black px-2 py-1.5 text-right font-bold">
                        {{ number_format($row['total_price'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            @foreach($manpower as $row)
                <tr>
                    <td class="border border-black px-2 py-1.5 text-center">{{ $globalIndex++ }}</td>
                    <td class="border border-black px-2 py-1.5 font-medium italic">{{ $row['job_position_name'] ?? '-' }}</td>
                    <td class="border border-black px-2 py-1.5 text-center">Person</td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">{{ number_format($row['quantity'] ?? 0) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right font-bold">
                        @if(isset($row['total_monthly_cost']) && $row['total_monthly_cost'] > 0)
                            {{ number_format($row['total_monthly_cost'], 0, ',', '.') }}
                        @else
                            <span class="italic text-gray-300">Personnel</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if(count($items) === 0 && count($manpower) === 0)
                <tr>
                    <td colspan="5" class="border border-black px-2 py-3 text-center text-gray-400 italic font-mono">-- No Records in Snapshot --</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="bg-gray-100 font-bold border-t-2 border-black uppercase text-[10px]">
                <td colspan="4" class="border border-black px-2 py-2 text-right">TOTAL MONTHLY CHARGE (BEFORE)</td>
                <td class="border border-black px-2 py-2 text-right text-sm tracking-tighter">
                    @php
                        $totalItems = collect($items)->sum(fn($i) => (float)($i['total_price'] ?? 0));
                        $totalMP = collect($manpower)->sum(fn($m) => (float)($m['total_monthly_cost'] ?? 0));
                        $grandTotal = (float)$totalItems + (float)$totalMP;
                    @endphp
                    IDR {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
