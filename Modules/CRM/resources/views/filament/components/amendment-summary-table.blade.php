<div class="mt-4 border border-black p-4 bg-gray-50/50">
    <div class="text-xs font-bold uppercase mb-2 border-b border-black pb-1">II. Amendment Summary</div>
    <table class="w-full text-xs border-collapse">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-black px-2 py-1 text-left w-1/3">Component</th>
                <th class="border border-black px-2 py-1 text-right">Before</th>
                <th class="border border-black px-2 py-1 text-right">After</th>
                <th class="border border-black px-2 py-1 text-right">Delta</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Before Data: Sum both items and manpower
                $beforeAmountItems = collect($before['items'] ?? [])->sum(fn($i) => (float)($i['total_price'] ?? 0));
                $beforeAmountMP = collect($before['manpower_details'] ?? [])->sum(fn($m) => (float)($m['total_monthly_cost'] ?? 0));
                $beforeAmount = (float)$beforeAmountItems + (float)$beforeAmountMP;
                
                $beforeQty = collect($before['manpower_details'] ?? [])->sum(fn($m) => (float)($m['quantity'] ?? 0));

                // After Data: Sum everything from the unified list
                $afterAmount = collect($after)->sum(fn($i) => (float)($i['total_price'] ?? 0));
                $afterQty = collect($after)->where('type', 'personnel')->sum(fn($m) => (float)($m['quantity'] ?? 0));

                // Delta
                $deltaAmount = (float)$afterAmount - (float)$beforeAmount;
                $deltaQty = (float)$afterQty - (float)$beforeQty;
            @endphp
            <tr>
                <td class="border border-black px-2 py-1 font-medium">Total Monthly Service Amount</td>
                <td class="border border-black px-2 py-1 text-right font-mono">{{ number_format($beforeAmount, 0, ',', '.') }}</td>
                <td class="border border-black px-2 py-1 text-right font-mono font-bold">{{ number_format($afterAmount, 0, ',', '.') }}</td>
                <td class="border border-black px-2 py-1 text-right font-mono {{ $deltaAmount > 0 ? 'text-green-600' : ($deltaAmount < 0 ? 'text-red-600' : 'text-gray-400') }}">
                    {{ $deltaAmount > 0 ? '+' : '' }}{{ number_format($deltaAmount, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="border border-black px-2 py-1 font-medium">Total Personnel Headcount</td>
                <td class="border border-black px-2 py-1 text-right">{{ number_format($beforeQty) }} Person(s)</td>
                <td class="border border-black px-2 py-1 text-right font-bold">{{ number_format($afterQty) }} Person(s)</td>
                <td class="border border-black px-2 py-1 text-right {{ $deltaQty > 0 ? 'text-green-600' : ($deltaQty < 0 ? 'text-red-600' : 'text-gray-400') }}">
                    {{ $deltaQty > 0 ? '+' : '' }}{{ $deltaQty }} Person(s)
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="bg-gray-200 font-bold uppercase text-[10px]">
                <td colspan="4" class="px-2 py-1 border border-black italic text-gray-500">
                    * Values derived from unified amendment entries above.
                </td>
            </tr>
        </tfoot>
    </table>
</div>
