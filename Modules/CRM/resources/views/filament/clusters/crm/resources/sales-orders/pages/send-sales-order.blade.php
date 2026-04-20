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
                    <div class="font-medium text-lg text-gray-900">{{ $data['subject'] ?? '' }}</div>
                </div>

                <div class="prose max-w-none">
                    <div
                        style="font-family: sans-serif; line-height: 1.6; color: #333; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <h2 style="color: #2563eb; margin-top: 0;">Sales Order - {{ $record->so_number }}</h2>

                        <p>Dear {{ $data['recipient_name'] ?? $record->customer?->name }},</p>

                        @if (!empty($data['message']))
                            <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($data['message']) }}
                            </div>
                        @else
                           <p>A new Sales Order #{{ $record->so_number }} has been generated for your review.</p>
                        @endif

                        <p>Please review the document details and contact our representative for any further actions.</p>

                        <p>Best regards,<br>
                            <strong>{{ auth()->user()?->name ?? 'GDPS ERP System' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-dashed">
                    <div class="flex items-center gap-2 text-sm text-gray-400 italic">
                        <x-filament::icon icon="heroicon-m-paper-clip" class="h-4 w-4" />
                        <span>so-{{ str_replace(['/', '\\'], '-', $record->so_number) }}.pdf</span>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
