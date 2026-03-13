<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Email Details
            </x-slot>

            <form wire:submit.prevent="sendEmail" class="space-y-6">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-4 justify-start">
                    <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                        Send Email
                    </x-filament::button>
                </div>
            </form>
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
                    <div style="font-family: sans-serif; line-height: 1.6; color: #333; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <h2 style="color: #2563eb; margin-top: 0;">Proposal - {{ $record->proposal_number }}</h2>
                        
                        <p>Dear {{ $record->customer?->name }},</p>
                        
                        @if(!empty($data['message']))
                            <div style="background-color: #f9fafb; padding: 15px; border-radius: 6px; border-left: 4px solid #3b82f6; margin: 20px 0; white-space: pre-wrap;">{{ $data['message'] }}</div>
                        @else
                            <p>Please find the attached proposal for our services.</p>
                        @endif
                        
                        <p>If you have any questions or require further information, please do not hesitate to contact us.</p>
                        
                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                            <p style="margin: 0;">Best regards,</p>
                            <p style="margin: 5px 0 0 0; font-weight: bold; color: #1f2937;">GDPS ERP System</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-dashed">
                    <div class="flex items-center gap-2 text-sm text-gray-400 italic">
                        <x-filament::icon
                            icon="heroicon-m-paper-clip"
                            class="h-4 w-4"
                        />
                        <span>proposal-{{ str_replace(['/', '\\'], '-', $record->proposal_number) }}.pdf</span>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
