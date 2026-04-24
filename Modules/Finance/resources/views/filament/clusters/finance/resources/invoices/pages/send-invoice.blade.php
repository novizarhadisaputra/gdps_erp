<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Email Details
            </x-slot>

            <div class="space-y-6">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-4 justify-start">
                    {{ $this->sendEmailAction }}
                </div>
            </div>
        </x-filament::section>

        {{-- Preview Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Live Preview
            </x-slot>

            <div class="border rounded-lg p-6 bg-white min-h-[400px]">
                <div class="mb-4 pb-4 border-b">
                    <div class="text-sm text-gray-500">Subject:</div>
                    @php
                        $subject = $this->form->getRawState()['subject'] ?? '';
                        $subject = is_array($subject) ? '' : (string) $subject;
                    @endphp
                    <div class="font-medium text-lg text-gray-900">{{ $subject }}</div>
                </div>

                <div class="prose max-w-none">
                    <div
                        style="font-family: sans-serif; line-height: 1.6; color: #333; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        @php
                            $formData = $this->form->getRawState();
                            $recipientName = $formData['recipient_name'] ?? ($record->customer?->name ?? 'Customer');
                            $recipientName = is_array($recipientName) ? 'Customer' : (string) $recipientName;
                        @endphp

                        <h2 style="color: #2563eb; margin-top: 0;">Hello, {{ $recipientName }}</h2>

                        @if (!empty($formData['message'] ?? null))
                            <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($formData['message']) }}
                            </div>
                        @else
                            <p>Please find your invoice attached.</p>
                        @endif

                        <p>Best regards,<br>
                            <strong>{{ auth()->user()?->name ?? 'GDPS ERP System' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-dashed">
                    <div class="flex items-center gap-2 text-sm text-gray-400 italic">
                        <x-filament::icon icon="heroicon-m-paper-clip" class="h-4 w-4" />
                        <span>Invoice-{{ $record->invoice_number }}.pdf</span>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
