<x-layouts.guest>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100">
                <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Proposal Signed!
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Thank you for your response. The proposal <span class="font-bold text-gray-900">#{{ $proposal->proposal_number }}</span> has been successfully approved and signed.
            </p>
            <p class="text-xs text-gray-400 mt-4">
                A confirmation has been sent to our sales team. You can now close this window.
            </p>

            <div class="mt-8">
                <a href="#" onclick="window.close()" class="text-blue-600 hover:text-blue-500 font-medium">
                    Close Window
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>
