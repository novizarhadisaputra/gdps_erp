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
            \Filament\Actions\Action::make('generateGI')
                ->label('Generate General Information')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->visible(fn () => $this->record->lead->generalInformations()->doesntExist() && ! empty($this->record->revenue_distribution_planning))
                ->requiresConfirmation()
                ->modalHeading('Generate General Information')
                ->modalDescription('Apakah Anda yakin ingin membuat data General Information (GI) berdasarkan Sales Plan ini?')
                ->action(function () {
                    $this->record->toGeneralInformation();

                    \Filament\Notifications\Notification::make()
                        ->title('General Information Created')
                        ->body('Data has been synced from Sales Plan.')
                        ->success()
                        ->send();

                    return redirect()->to(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::getUrl('general-informations', ['record' => $this->record->lead_id]));
                }),
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
