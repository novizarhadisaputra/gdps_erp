<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\SalesPlanResource;

class ViewSalesPlan extends ViewRecord
{
    protected static string $resource = SalesPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            ActionGroup::make([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(function () {
                        $pdf = Pdf::loadView('crm::pdf.sales_plan', ['record' => $this->record]);
                        $name = str_replace(['/', '\\'], '-', $this->record->project_code ?? 'Draft');
                        $leadName = \Illuminate\Support\Str::slug($this->record->lead?->company_name ?? $this->record->lead?->title ?? 'Unknown-Lead', '-');
                        $fileName = "SalesPlan_{$name}_{$leadName}.pdf";

                        return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                    }),

                Action::make('generateGI')
                    ->label('Convert to GI')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('success')
                    ->visible(fn () => $this->record->lead->generalInformations()->doesntExist() && ! empty($this->record->revenue_distribution_planning))
                    ->requiresConfirmation()
                    ->modalHeading('Generate General Information')
                    ->modalDescription('Apakah Anda yakin ingin membuat data General Information (GI) berdasarkan Sales Plan ini?')
                    ->action(function () {
                        $this->record->toGeneralInformation();

                        Notification::make()
                            ->title('General Information Created')
                            ->body('Data has been synced from Sales Plan.')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $this->record->lead_id]));
                    }),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisVertical)
                ->color('primary')
                ->button(),

            DeleteAction::make(),
        ];
    }
}
