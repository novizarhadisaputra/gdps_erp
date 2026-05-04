<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        <x-filament::section>
            <x-slot name="heading">
                Financial Split Configuration
            </x-slot>

            <x-slot name="description">
                Specify how the BAPP total should be distributed across different revenue types and GL accounts.
            </x-slot>

            <form wire:submit="generate">
                <div class="space-y-6">
                    {{ $this->form }}

                    <div class="flex flex-wrap items-center gap-4 justify-start mt-6 pt-6 border-t border-gray-200">
                        {{ $this->generateAction }}
                    </div>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
