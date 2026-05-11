<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Branding/Contextual Header (Optional but adds premium feel) --}}
        <div class="p-6 bg-white border border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800 rounded-xl">
            <div class="flex items-start gap-4">
                <div class="p-3 text-primary-600 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <x-heroicon-o-document-check class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">BAPP Financial Review</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Finalize the invoicing process by reviewing revenue distribution and expense recognition.
                        This action will synchronize the work completion record with the accrual and create a draft invoice.
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="generate">
            <div class="space-y-6">
                {{ $this->form }}
            </div>
        </form>

        {{-- Footer/Bottom Actions are handled by Page Header Actions for premium Filament feel --}}
    </div>
</x-filament-panels::page>
