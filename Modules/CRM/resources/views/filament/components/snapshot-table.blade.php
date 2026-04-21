<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
        <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
            <tr>
                @foreach($headers as $header)
                    <th scope="col" class="px-4 py-3">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($rows as $row)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                    @foreach($mapping as $key)
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                            @if(str_contains($key, 'price') || str_contains($key, 'amount'))
                                IDR {{ number_format($row[$key] ?? 0, 2) }}
                            @else
                                {{ $row[$key] ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-4 py-8 text-center text-gray-400 italic">
                        No data recorded in this snapshot.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
