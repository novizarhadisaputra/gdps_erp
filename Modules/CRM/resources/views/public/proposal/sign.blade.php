<x-layouts.guest>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-blue-600">
                    Proposal Signature Portal
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Proposal Number: <span class="font-bold text-gray-900">{{ $proposal->proposal_number }}</span>
                </p>

                @if($errors->has('message'))
                    <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="text-sm font-bold">{{ $errors->first('message') }}</p>
                    </div>
                @endif
            </div>

            <div class="border-t border-b border-gray-100 py-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Proposal Preview</h3>
                <div class="bg-gray-50 p-6 rounded-xl overflow-auto max-h-[500px] prose max-w-none border border-gray-200">
                    @if($latestLog)
                        {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($latestLog->message) }}
                    @else
                        <p class="text-gray-400 italic">No proposal content found.</p>
                    @endif
                </div>
            </div>

            <form action="{{ URL::temporarySignedRoute('proposals.public.submit', now()->addMinutes(30), ['proposal' => $proposal]) }}" method="POST" id="signature-form" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label for="signer_name" class="block text-sm font-medium text-gray-700">Full Name of Signer</label>
                        <input type="text" name="signer_name" id="signer_name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="e.g. John Doe">
                        <p class="mt-1 text-xs text-gray-400 italic">Person who is actually signing the document.</p>
                    </div>
                    <div>
                        <label for="signer_title" class="block text-sm font-medium text-gray-700">Job Title / Position</label>
                        <input type="text" name="signer_title" id="signer_title" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="e.g. Director">
                    </div>
                    <div class="md:col-span-2">
                        <label for="signed_proposal" class="block text-sm font-medium text-gray-700">Signed Proposal File (Optional)</label>
                        <div class="mt-1 flex items-center">
                            <input type="file" name="signed_proposal" id="signed_proposal" accept=".pdf,.png,.jpg,.jpeg"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <p class="mt-1 text-xs text-gray-400 italic">Upload scanned copy or physical signature if available (PDF or Image).</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">Digital Signature</label>
                    <div class="relative bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-4">
                        <canvas id="signature-pad" class="w-full h-64 border bg-white rounded-lg cursor-crosshair"></canvas>
                        <button type="button" id="clear-signature" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <input type="hidden" name="signature_data" id="signature_data">
                    <p class="text-xs text-gray-500">I hereby confirm that I have read and agreed to the terms in this proposal.</p>
                </div>

                <div class="mt-8 flex justify-center">
                    <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Approve & Sign Proposal
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                minWidth: 1.5,
                maxWidth: 3.5
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const oldData = signaturePad.toData();
                
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                
                signaturePad.clear();
                signaturePad.fromData(oldData);
            }

            let resizeTimeout;
            window.addEventListener("resize", () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(resizeCanvas, 200);
            });
            
            // Initial resize
            resizeCanvas();

            const form = document.getElementById('signature-form');
            form.addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please provide a signature before submitting.');
                    return;
                }
                document.getElementById('signature_data').value = signaturePad.toDataURL();
            });

            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
            });
        });
    </script>
    <style>
        .prose p { margin-bottom: 1em; }
    </style>
    @endpush
</x-layouts.guest>
