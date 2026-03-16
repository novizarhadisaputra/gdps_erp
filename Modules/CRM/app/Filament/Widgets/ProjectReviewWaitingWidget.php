<?php

namespace Modules\CRM\Filament\Widgets;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\ProjectReview;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;

class ProjectReviewWaitingWidget extends TableWidget
{
    protected static ?string $heading = 'Project Reviews Needing My Attention';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $user = auth()->user();
                /** @var SignatureService $service */
                $service = app(SignatureService::class);

                // 1. Find all GI needing approval
                $giIds = GeneralInformation::query()
                    ->where('status', GeneralInformationStatus::Submitted)
                    ->get()
                    ->filter(fn ($record) => $this->isEligible($record, $service, $user))
                    ->pluck('id');

                // 2. Find all PA needing approval
                $paIds = ProfitabilityAnalysis::query()
                    ->where('status', ProfitabilityAnalysisStatus::Submitted)
                    ->get()
                    ->filter(fn ($record) => $this->isEligible($record, $service, $user))
                    ->pluck('id');

                // 3. Find all Proposals needing approval
                $proposalIds = Proposal::query()
                    ->where('status', ProposalStatus::Submitted)
                    ->get()
                    ->filter(fn ($record) => $this->isEligible($record, $service, $user))
                    ->pluck('id');

                // Find Lead IDs associated with any of these
                $leadIds = collect()
                    ->merge(GeneralInformation::whereIn('id', $giIds)->pluck('lead_id'))
                    ->merge(ProfitabilityAnalysis::whereIn('id', $paIds)->pluck('lead_id'))
                    ->merge(Proposal::whereIn('id', $proposalIds)->pluck('lead_id'))
                    ->unique();

                // Ensure ProjectReview records exist for these Leads
                foreach ($leadIds as $leadId) {
                    ProjectReview::firstOrCreate(['lead_id' => $leadId]);
                }

                return ProjectReview::query()
                    ->whereIn('lead_id', $leadIds)
                    ->with(['lead', 'generalInformation', 'profitabilityAnalysis', 'proposal']);
            })
            ->columns([
                TextColumn::make('lead.company_name')
                    ->label('Company / Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('generalInformation.document_number')
                    ->label('GI #')
                    ->placeholder('-'),
                TextColumn::make('profitabilityAnalysis.document_number')
                    ->label('PA #')
                    ->placeholder('-'),
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal #')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->label('Overall Status'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (ProjectReview $record) => \Modules\CRM\Filament\Clusters\CRM\Resources\ProjectReview\ProjectReviewResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected function isEligible($record, SignatureService $service, $user): bool
    {
        $required = $service->getRequiredApprovers($record);
        $nextRule = $required->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

        return $nextRule && $service->isEligibleApprover($nextRule, $user);
    }
}
