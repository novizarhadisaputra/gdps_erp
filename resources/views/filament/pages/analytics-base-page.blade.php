<x-filament-panels::page>
    <div class="space-y-6">
        @if (method_exists($this, 'getWidgets'))
            <x-filament-widgets::widgets :widgets="$this->getWidgets()" :columns="$this->getWidgetColumns()" />
        @endif
    </div>
</x-filament-panels::page>
