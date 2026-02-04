<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

abstract class AnalyticsBasePage extends Page
{
    protected string $view = 'filament.pages.analytics-base-page';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('$refresh');
                    $this->dispatch('refreshWidgets');
                }),

            // Future implementation: Export actions
            // Action::make('exportPdf')
            //     ->label('Export PDF')
            //     ->icon('heroicon-m-document-text')
            //     ->action(fn () => $this->exportToPdf()),
            //
            // Action::make('exportExcel')
            //     ->label('Export Excel')
            //     ->icon('heroicon-m-table-cells')
            //     ->action(fn () => $this->exportToExcel()),
        ];
    }

    protected function exportToPdf(): void
    {
        // To be implemented by child classes
        $this->notify('info', 'PDF export will be implemented soon');
    }

    protected function exportToExcel(): void
    {
        // To be implemented by child classes
        $this->notify('info', 'Excel export will be implemented soon');
    }

    protected function notify(string $type, string $message): void
    {
        $this->dispatch('notify', [
            'type' => $type,
            'message' => $message,
        ]);
    }
}
