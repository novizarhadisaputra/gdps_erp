<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class CreateProposal extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Proposal Title')
                ->placeholder('e.g. Outsourcing Services for April')
                ->required()
                ->default(function () {
                    $lead = $this->parentRecord;
                    $customerName = $lead?->customer?->name ?? 'New';
                    return $customerName . ' Proposal';
                }),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['lead_id'] = $lead->id;
            $data['customer_id'] = $lead->customer_id;

            // Find latest approved or submitted PA
            $latestPA = $lead->profitabilityAnalyses()
                ->whereIn('status', [
                    \Modules\Finance\Enums\ProfitabilityAnalysisStatus::Approved,
                    \Modules\Finance\Enums\ProfitabilityAnalysisStatus::Submitted,
                ])
                ->latest('created_at')
                ->first();

            if ($latestPA) {
                $data['profitability_analysis_id'] = $latestPA->id;
                $data['work_scheme_id'] = $latestPA->work_scheme_id;
                $data['amount'] = $latestPA->revenue_per_month;
            } else {
                $data['work_scheme_id'] = $lead->work_scheme_id;
                $data['amount'] = $lead->estimated_amount;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->getRecord(),
            'lead' => $this->parentRecord,
        ]);
    }
}
