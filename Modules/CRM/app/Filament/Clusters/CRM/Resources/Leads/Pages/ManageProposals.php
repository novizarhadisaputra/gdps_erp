<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Create and track project proposals for this lead.';
    }

    protected static string $relationship = 'proposals';

    protected static ?string $relatedResource = ProposalResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Lead Proposals';

    public function form(Schema $schema): Schema
    {
        return ProposalResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ProposalResource::table($table)
            ->headerActions([
                Action::make('bookingCode')
                    ->label('Booking Proposal Code')
                    ->icon('heroicon-o-document-plus')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Booking Proposal Code')
                    ->modalDescription('This will generate a new Proposal record with an auto-generated code and link it to this Lead. You can upload the documentation later.')
                    ->action(function () {
                        $lead = $this->getOwnerRecord();

                        // Find latest approved or submitted PA
                        $latestPA = $lead->profitabilityAnalyses()
                            ->whereIn('status', [
                                ProfitabilityAnalysisStatus::Approved,
                                ProfitabilityAnalysisStatus::Submitted,
                            ])
                            ->latest('created_at')
                            ->first();

                        Proposal::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'profitability_analysis_id' => $latestPA?->id,
                            'work_scheme_id' => $latestPA?->work_scheme_id ?? $lead->work_scheme_id,
                            'amount' => $lead->estimated_amount ?? 0,
                            'submission_date' => now(),
                            'status' => ProposalStatus::Draft,
                            'is_manual' => true,
                        ]);

                        $lead->update([
                            'status' => LeadStatus::Proposal,
                            'title' => ($lead->customer?->name ?? 'Lead').' Proposal',
                        ]);

                        Notification::make()
                            ->title('Proposal code booked successfully')
                            ->body('The lead title has been standardized for professionalism.')
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Proposal Code Booked'),
            ]);
    }
}
