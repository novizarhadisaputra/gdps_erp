<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\MasterData\Services\SignatureService;

class ViewGeneralInformation extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = GeneralInformationResource::class;

    public function getSubheading(): ?string
    {
        return 'Detailed view of project general information.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $record = $this->getRecord();
                        $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);
                        $name = Str::slug($record->document_number, '-');

                        return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$name}.pdf");
                    }),
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            ActionGroup::make([
                EditAction::make()
                    ->hidden(fn () => $this->getRecord()->isLocked()),

                Action::make('createPA')
                    ->label('Create PA')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('success')
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Approved)
                    ->action(function () {
                        $record = $this->getRecord();
                        $pa = $record->toProfitabilityAnalysis();
                        $lead = $record->lead;

                        Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('profitability-analyses', ['record' => $lead]));
                    }),

                Action::make('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Reject General Information')
                    ->modalDescription('Are you sure you want to reject this General Information? The status will return to Rejected and it can be edited again.')
                    ->action(function () {
                        $this->getRecord()->update(['status' => GeneralInformationStatus::Rejected]);
                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title('General Information Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Submitted),

                \Filament\Actions\DeleteAction::make(),
            ])
                ->label('Options')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray')
                ->button(),

            Action::make('incompleteWarning')
                ->label('Submit')
                ->color('gray')
                ->icon('heroicon-o-exclamation-triangle')
                ->disabled()
                ->tooltip('Harap lengkapi semua data wajib (Required) dan minimal 1 PIC untuk dapat melakukan Submit.')
                ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && ! $this->getRecord()->isComplete()),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update(['status' => GeneralInformationStatus::Submitted]);
                    app(SignatureService::class)->notifyNextApprovers($this->getRecord());
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && $this->getRecord()->isComplete()),

        ];
    }
}
