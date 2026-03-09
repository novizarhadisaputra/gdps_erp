<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityThreshold;
use Modules\MasterData\Services\SignatureService;

trait HasProfitabilityAnalysisActions
{
    protected function getSubmitAction(): Action
    {
        return Action::make('Submit')
            ->color('info')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'submitted']))
            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'draft');
    }

    protected function getApproveAction(): Action
    {
        return Action::make('Approve')
            ->color('success')
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'approved']))
            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'submitted');
    }

    protected function getRejectAction(): Action
    {
        return Action::make('Reject')
            ->color('danger')
            ->icon('heroicon-o-x-mark')
            ->requiresConfirmation()
            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'rejected']))
            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'submitted');
    }

    protected function getSignAction(): Action
    {
        return Action::make('Sign')
            ->label('Digital Signature')
            ->color('primary')
            ->icon('heroicon-o-pencil-square')
            ->form([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Masukkan PIN tanda tangan digital Anda.'),
            ])
            ->action(function (ProfitabilityAnalysis $record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('PIN Salah')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);

                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if (! $matchingRule) {
                    Notification::make()
                        ->title('Akses Ditolak')
                        ->body('Anda tidak memiliki otoritas untuk menandatangani dokumen ini berdasarkan aturan approval saat ini.')
                        ->warning()
                        ->send();

                    return;
                }

                if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                    Notification::make()
                        ->title('Sudah Ditandatangani')
                        ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
                        ->warning()
                        ->send();

                    return;
                }

                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                Notification::make()
                    ->title('Dokumen Berhasil Ditandatangani')
                    ->success()
                    ->send();

                if ($record->isFullyApproved()) {
                    $record->update(['status' => 'approved']);
                }
            })
            ->visible(fn (ProfitabilityAnalysis $record) => in_array($record->status, ['submitted', 'draft']));
    }

    protected function getGenerateProjectAction(): Action
    {
        return Action::make('generateProject')
            ->label('Generate Project')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->visible(fn (ProfitabilityAnalysis $record) => ! $record->project()->exists() &&
                $record->status === 'approved' &&
                $record->revenue_per_month !== null &&
                $record->margin_percentage !== null &&
                (! empty($record->analysis_details) || $record->items()->exists())
            )
            ->schema([
                TextInput::make('summary')
                    ->label('Summary')
                    ->default(fn (ProfitabilityAnalysis $record) => "You are about to generate a Project for '{$record->customer?->name}'. This will consume the next sequence number for this customer and work scheme.")
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('project_name_override')
                    ->label('Project Name (Optional)')
                    ->placeholder(fn (ProfitabilityAnalysis $record) => $record->proposal?->proposal_number ?? 'Project for '.$record->customer?->name),
            ])
            ->action(function (ProfitabilityAnalysis $record, array $data) {
                if (! $this->validateProfitability($record)) {
                    return;
                }

                $service = app(ProjectGenerationService::class);

                $project = $service->generateFromPA($record);

                if (! empty($data['project_name_override'])) {
                    $project->update(['name' => $data['project_name_override']]);
                }

                Notification::make()
                    ->title('Project Generated')
                    ->body("Project Code: {$project->code}")
                    ->success()
                    ->send();
            });
    }

    protected function getCreateProposalAction(): Action
    {
        return Action::make('createProposal')
            ->label('Create Proposal')
            ->icon('heroicon-o-document-plus')
            ->color('primary')
            ->visible(fn (ProfitabilityAnalysis $record) => ! $record->proposal_id && $record->status === 'approved')
            ->schema([
                TextInput::make('amount')
                    ->default(fn (ProfitabilityAnalysis $record) => $record->revenue_per_month)
                    ->numeric()
                    ->prefix('IDR')
                    ->required(),
                DatePicker::make('submission_date')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (ProfitabilityAnalysis $record, array $data) {
                if (! $this->validateProfitability($record)) {
                    return;
                }

                $proposal = Proposal::create([
                    'customer_id' => $record->customer_id,
                    'lead_id' => $record->lead_id,
                    'profitability_analysis_id' => $record->id,
                    'work_scheme_id' => $record->work_scheme_id,
                    'amount' => $data['amount'],
                    'submission_date' => $data['submission_date'],
                    'status' => ProposalStatus::Draft,
                ]);

                $record->update(['proposal_id' => $proposal->id]);
                $record->lead->update(['status' => LeadStatus::Proposal]);

                Notification::make()
                    ->title('Proposal Created')
                    ->success()
                    ->send();
            });
    }

    public function getEditManpowerAction(): Action
    {
        return Action::make('edit_manpower')
            ->label('Edit Manpower')
            ->icon('heroicon-o-users')
            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 3))
            ->action(fn (ProfitabilityAnalysis $record, array $data) => $record->update($data))
            ->modalHeading('Edit Manpower Costing')
            ->visible(fn (ProfitabilityAnalysis $record) => ! $record->is_manual_cost && in_array($record->status, ['draft', 'rejected']));
    }

    public function getEditOperationalAction(): Action
    {
        return Action::make('edit_operational')
            ->label('Edit Operational')
            ->icon('heroicon-o-wrench-screwdriver')
            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 4))
            ->action(fn (ProfitabilityAnalysis $record, array $data) => $record->update($data))
            ->modalHeading('Edit Operational Costing')
            ->visible(fn (ProfitabilityAnalysis $record) => ! $record->is_manual_cost && in_array($record->status, ['draft', 'rejected']));
    }

    public function getEditManualAction(): Action
    {
        return Action::make('edit_manual')
            ->label('Edit Manual Costs')
            ->icon('heroicon-o-banknotes')
            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 5))
            ->action(fn (ProfitabilityAnalysis $record, array $data) => $record->update($data))
            ->modalHeading('Edit Manual Cost Breakdown')
            ->visible(fn (ProfitabilityAnalysis $record) => $record->is_manual_cost && in_array($record->status, ['draft', 'rejected']));
    }

    public function getEditIndirectAction(): Action
    {
        return Action::make('edit_indirect')
            ->label('Edit Indirect Costs')
            ->icon('heroicon-o-presentation-chart-line')
            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 6))
            ->action(fn (ProfitabilityAnalysis $record, array $data) => $record->update($data))
            ->modalHeading('Edit Indirect Costing')
            ->visible(fn (ProfitabilityAnalysis $record) => in_array($record->status, ['draft', 'rejected']));
    }

    public function getStepActions(): array
    {
        return [
            $this->getEditManpowerAction(),
            $this->getEditOperationalAction(),
            $this->getEditManualAction(),
            $this->getEditIndirectAction(),
        ];
    }

    protected function getProfitabilityAnalysisActions(): array
    {
        return [
            $this->getSignAction(),
            $this->getSubmitAction(),
            $this->getApproveAction(),
            $this->getRejectAction(),
            $this->getCreateProposalAction(),
        ];
    }

    protected function validateProfitability(ProfitabilityAnalysis $record): bool
    {
        $threshold = ProfitabilityThreshold::first();
        if (! $threshold) {
            return true;
        }

        if ($record->margin_percentage < $threshold->min_gpm) {
            Notification::make()
                ->title('GPM Below Threshold')
                ->body('The Gross Profit Margin ('.number_format($record->margin_percentage, 2).'%) is below the required minimum of '.number_format($threshold->min_gpm, 2).'%.')
                ->danger()
                ->send();

            return false;
        }

        if ($record->net_profit_margin < $threshold->min_npm) {
            Notification::make()
                ->title('NPM Below Threshold')
                ->body('The Net Profit Margin ('.number_format($record->net_profit_margin, 2).'%) is below the required minimum of '.number_format($threshold->min_npm, 2).'%.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }
}
