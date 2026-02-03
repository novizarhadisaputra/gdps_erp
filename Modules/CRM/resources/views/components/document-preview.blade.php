<div class="flex items-center justify-center w-full h-full min-h-[500px]">
    @if (str_contains($type, 'pdf'))
        <iframe src="{{ $url }}" class="w-full h-[80vh] border-0" frameborder="0"></iframe>
    @elseif (str_contains($type, 'image'))
        <img src="{{ $url }}" class="max-w-full max-h-[80vh] object-contain" alt="Document Preview">
    @else
        <div class="text-center p-10">
            <x-heroicon-o-document class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <p class="text-lg font-medium text-gray-600 dark:text-gray-300">File cannot be previewed directly.</p>
            <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline mt-2 inline-block">
                Download / Open in New Tab
            </a>
        </div>
    @endif
</div>
