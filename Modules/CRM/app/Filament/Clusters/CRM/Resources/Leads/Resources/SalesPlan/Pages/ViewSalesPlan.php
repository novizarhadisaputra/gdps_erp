<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\SalesPlanResource;

class ViewSalesPlan extends ViewRecord
{
    protected static string $resource = SalesPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.sales_plan', ['record' => $this->record]);
                    $filename = str_replace(['/', '\\'], '-', $this->record->document_number);

                    return response()->streamDownload(fn () => print ($pdf->output()), "sales-plan-{$filename}.pdf");
                }),
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
