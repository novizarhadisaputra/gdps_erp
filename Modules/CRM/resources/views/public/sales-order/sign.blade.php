<x-layouts.guest>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl">

            {{-- Branded Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-6">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logo.png') }}" alt="GDPS Logo" class="h-14 w-auto object-contain">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">PT GARUDA DAYA PRATAMA SEJAHTERA</p>
                        <p class="text-lg font-extrabold text-gray-800">Sales Order Signature Portal</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Order Number</p>
                    <p class="text-sm font-bold text-blue-600">{{ $salesOrder->so_number }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ now()->format('d M Y') }}</p>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                    <ul class="list-disc list-inside text-sm font-bold">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="border-t border-b border-gray-100 py-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Order Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Customer</p>
                        <p class="font-medium">{{ $salesOrder->customer?->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Service Type</p>
                        <p class="font-medium">{{ $salesOrder->service_type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Amount</p>
                        <p class="font-medium">IDR {{ number_format($salesOrder->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Job Location</p>
                        <p class="font-medium">{{ $salesOrder->job_location }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ URL::temporarySignedRoute('sales_orders.public.submit', now()->addMinutes(60), ['sales_order' => $salesOrder]) }}" method="POST" id="signature-form" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label for="signer_name" class="block text-sm font-medium text-gray-700">Full Name of Signer <span class="text-red-500">*</span></label>
                        <input type="text" name="signer_name" id="signer_name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="e.g. John Doe" value="{{ old('signer_name') }}">
                    </div>
                    <div>
                        <label for="signer_title" class="block text-sm font-medium text-gray-700">Job Title / Position <span class="text-red-500">*</span></label>
                        <input type="text" name="signer_title" id="signer_title" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="e.g. Director" value="{{ old('signer_title') }}">
                    </div>
                    <div class="md:col-span-2">
                        <label for="sender_email" class="block text-sm font-medium text-gray-700">Your Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="sender_email" id="sender_email" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="e.g. youremail@company.com" value="{{ old('sender_email', $salesOrder->customer?->email) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label for="signed_document" class="block text-sm font-medium text-gray-700">Signed Document File <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex items-center">
                            <input type="file" name="signed_document" id="signed_document" accept=".pdf,.png,.jpg,.jpeg" required
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
                    <p class="text-xs text-gray-500">Optional — draw your digital signature above.</p>
                </div>

                <div class="mt-8 flex justify-center">
                    <button type="button" id="open-confirm-modal"
                            class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Submit Signature
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Submission</h3>
            <p class="text-sm text-gray-600 mb-6">
                By clicking <strong>Confirm & Submit</strong>, you confirm that you have reviewed this Sales Order and agree to its terms. This action cannot be undone.
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button" id="close-confirm-modal"
                        class="px-5 py-2 rounded-md text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">
                    Cancel
                </button>
                <button type="button" id="confirm-submit"
                        class="px-5 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Confirm & Submit
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            const form = document.getElementById('signature-form');
            const modal = document.getElementById('confirm-modal');

            document.getElementById('open-confirm-modal').addEventListener('click', () => {
                if (!signaturePad.isEmpty()) {
                    document.getElementById('signature_data').value = signaturePad.toDataURL();
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            document.getElementById('close-confirm-modal').addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            document.getElementById('confirm-submit').addEventListener('click', () => {
                form.submit();
            });

            document.getElementById('clear-signature').addEventListener('click', () => {
                signaturePad.clear();
            });
        });
    </script>
    @endpush
</x-layouts.guest>
