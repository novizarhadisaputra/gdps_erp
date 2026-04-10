<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class EditProposal extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('downloadPdf')
                ->label('Download Draft PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $this->getRecord()->load([
                        'customer',
                        'profitabilityAnalysis.workScheme',
                        'profitabilityAnalysis.paymentTerm',
                        'profitabilityAnalysis.productCluster',
                        'lead.user',
                        'lead.ams',
                        'lead.manpowerTemplates.items.jobPosition',
                        'lead.costingTemplates.costingTemplateItems.item',
                        'lead.latestGeneralInformation',
                        'lead.salesPlan',
                    ]);

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $this->getRecord()])
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'isRemoteEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'defaultFont' => 'sans-serif',
                        ]);
                    $filename = str_replace(['/', '\\'], '-', $this->getRecord()->proposal_number);

                    return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$filename}.pdf");
                }),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record, 'lead' => $this->record->lead_id]);
    }
}
