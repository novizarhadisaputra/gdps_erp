<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
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
            ->action(fn ($record) => $record->update(['status' => ProfitabilityAnalysisStatus::Submitted]))
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);

                return $status === ProfitabilityAnalysisStatus::Draft || $status === 'draft';
            });
    }

    protected function getApproveMarginAction(): Action
    {
        return Action::make('Approve Margin')
            ->color('success')
            ->icon('heroicon-o-check-badge')
            ->requiresConfirmation()
            ->action(function ($record) {
                $record->update(['is_margin_approved' => true]);

                Notification::make()
                    ->title('Margin Approved')
                    ->body('Net Profit Margin has been approved. The process can now proceed to Proposal.')
                    ->success()
                    ->send();
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                $isMarginApproved = $record?->is_margin_approved ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->is_margin_approved : false);

                return ($status === ProfitabilityAnalysisStatus::Draft || $status === 'draft') && ! $isMarginApproved;
            });
    }

    protected function getRejectAction(): Action
    {
        return Action::make('Reject')
            ->color('danger')
            ->icon('heroicon-o-x-mark')
            ->requiresConfirmation()
            ->action(fn ($record) => $record->update(['status' => ProfitabilityAnalysisStatus::Rejected]))
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);

                return $status === ProfitabilityAnalysisStatus::Submitted || $status === 'submitted';
            });
    }

    protected function getSignAction(): Action
    {
        return Action::make('Sign')
            ->label('Digital Signature')
            ->color('primary')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Masukkan PIN tanda tangan digital Anda.'),
            ])
            ->action(function ($record, array $data) {
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
                    $record->update(['status' => ProfitabilityAnalysisStatus::Approved]);
                }
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                $allowed = [
                    ProfitabilityAnalysisStatus::Submitted,
                    'submitted',
                ];

                return in_array($status, $allowed, true);
            });
    }

    protected function getGenerateProjectAction(): Action
    {
        return Action::make('generateProject')
            ->label('Generate Project')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->visible(function ($record) {
                if (! $record && method_exists($this, 'getRecord')) {
                    $record = $this->getRecord();
                }

                return $record
                    && ! $record->project()->exists()
                    && $record->status === ProfitabilityAnalysisStatus::Approved
                    && $record->revenue_per_month !== null
                    && $record->margin_percentage !== null
                    && (! empty($record->analysis_details) || $record->items()->exists());
            })
            ->schema([
                TextInput::make('summary')
                    ->label('Summary')
                    ->default(fn ($record) => "You are about to generate a Project for '".($record?->customer?->name ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->customer?->name : ''))."'. This will consume the next sequence number for this customer and work scheme.")
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('project_name_override')
                    ->label('Project Name (Optional)')
                    ->placeholder(fn ($record) => ($record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null))?->proposal?->proposal_number ?? 'Project for '.($record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null))?->customer?->name),
            ])
            ->action(function ($record, array $data) {
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
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return $rec && ! $rec->proposal && $rec->status === ProfitabilityAnalysisStatus::Approved;
            })
            ->schema([
                TextInput::make('amount')
                    ->default(fn ($record) => ($record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null))?->revenue_per_month)
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->prefix('IDR ')
                    ->required(),
                DatePicker::make('submission_date')
                    ->default(now())
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                if (! $this->validateProfitability($record)) {
                    return;
                }

                $proposal = Proposal::create([
                    'customer_id' => $record->customer_id,
                    'lead_id' => $record->lead_id,
                    'profitability_analysis_id' => $record->id,
                    'work_scheme_id' => $record->work_scheme_id,
                    'amount' => is_numeric($data['amount']) ? (float) $data['amount'] : (float) str_replace(['.', ','], ['', '.'], $data['amount']),
                    'submission_date' => $data['submission_date'],
                    'status' => ProposalStatus::Draft,
                ]);

                $record->updateQuietly(['proposal_id' => $proposal->id]);
                $record->lead?->update(['status' => LeadStatus::Proposal]);

                Notification::make()
                    ->title('Proposal Created')
                    ->success()
                    ->send();

                return redirect(request()->header('Referer'));
            });
    }

    protected function getDuplicateAction(): Action
    {
        return Action::make('duplicate')
            ->label('Duplicate')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (ProfitabilityAnalysis $record) {
                try {
                    DB::transaction(function () use ($record, &$newRecord) {
                        $newRecord = $record->replicate([
                            'document_number',
                            'year',
                            'sequence_number',
                            'status',
                            'proposal_id',
                            'project_number',
                        ]);

                        $newRecord->status = ProfitabilityAnalysisStatus::Draft;
                        $newRecord->save();

                        // Duplicate items
                        foreach ($record->items as $item) {
                            $newItem = $item->replicate(['profitability_analysis_id']);
                            $newItem->profitability_analysis_id = $newRecord->id;
                            $newItem->save();
                        }

                        // Copy media (TOR, RFP, RFI)
                        foreach (['tor', 'rfp', 'rfi'] as $collection) {
                            $media = $record->getFirstMedia($collection);
                            if ($media) {
                                $media->copy($newRecord, $collection);
                            }
                        }
                    });

                    Notification::make()
                        ->title('Profitability Analysis Duplicated')
                        ->success()
                        ->send();

                    $resource = method_exists($this, 'getResource')
                        ? $this->getResource()
                        : \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::class;

                    $parameters = ['record' => $newRecord];
                    if (str_contains($resource, 'Modules\CRM')) {
                        $parameters['lead'] = $newRecord->lead_id;
                    }

                    return redirect()->to(
                        $resource::getUrl('view', $parameters)
                    );
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Duplication Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function getEditManpowerAction(): Action
    {
        return Action::make('edit_manpower')
            ->label('Edit Manpower')
            ->icon('heroicon-o-users')
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 3))
            ->action(fn ($record, array $data) => $record->update($data))
            ->modalHeading('Edit Manpower Costing')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ! ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditOperationalAction(): Action
    {
        return Action::make('edit_operational')
            ->label('Edit Operational')
            ->icon('heroicon-o-wrench-screwdriver')
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 4))
            ->action(fn ($record, array $data) => $record->update($data))
            ->modalHeading('Edit Operational Costing')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ! ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditManualAction(): Action
    {
        return Action::make('edit_manual')
            ->label('Edit Manual Costs')
            ->icon('heroicon-o-banknotes')
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 5))
            ->action(fn ($record, array $data) => $record->update($data))
            ->modalHeading('Edit Manual Cost Breakdown')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditIndirectAction(): Action
    {
        return Action::make('edit_indirect')
            ->label('Edit Indirect Costs')
            ->icon('heroicon-o-presentation-chart-line')
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 6))
            ->action(fn ($record, array $data) => $record->update($data))
            ->modalHeading('Edit Indirect Costing')
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);

                return in_array($status, ['draft', 'rejected']);
            });
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
            $this->getDuplicateAction(),
            $this->getApproveMarginAction(),
            $this->getSignAction(),
            $this->getSubmitAction(),
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
