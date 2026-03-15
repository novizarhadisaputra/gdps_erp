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
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
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
            ->action(function ($record) {
                $record->update(['status' => ProfitabilityAnalysisStatus::Submitted]);
                app(SignatureService::class)->notifyNextApprovers($record);
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($status === ProfitabilityAnalysisStatus::Draft->value || $status === 'draft') && ($rec?->isComplete() ?? false);
            });
    }

    protected function getIncompleteSubmitWarningAction(): Action
    {
        return Action::make('incompleteSubmitWarning')
            ->label('Submit')
            ->color('gray')
            ->icon('heroicon-o-exclamation-triangle')
            ->disabled()
            ->tooltip('Harap lengkapi semua data wajib (Required) dan minimal 1 item costing untuk dapat melakukan Submit.')
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($status === ProfitabilityAnalysisStatus::Draft->value || $status === 'draft') && ! ($rec?->isComplete() ?? false);
            });
    }

    protected function getApproveMarginAction(): Action
    {
        return Action::make('Approve Margin')
            ->color('success')
            ->icon('heroicon-o-check-badge')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve the margin.'),
            ])
            ->action(function ($record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $record->addSignature(auth()->user(), 'margin_approval');
                $record->update(['is_margin_approved' => true]);

                Notification::make()
                    ->title('Margin Approved')
                    ->body('Net Profit Margin has been approved and signed. The process can now proceed to Proposal.')
                    ->success()
                    ->send();
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                $isMarginApproved = $record?->is_margin_approved ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->is_margin_approved : false);

                return ($status === ProfitabilityAnalysisStatus::Submitted || $status === 'submitted') && ! $isMarginApproved;
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
                    ->helperText('Enter your digital signature PIN.'),
            ])
            ->action(function ($record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);

                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body('You do not have the authority to sign this document based on the current approval rules.')
                        ->warning()
                        ->send();

                    return;
                }

                // Find the first rule that is not yet satisfied
                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()
                        ->title('Already Signed')
                        ->body('This document has already been signed by the appropriate role(s) you represent.')
                        ->warning()
                        ->send();

                    return;
                }

                // Determine the role to record for this signature
                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    // Match user role against rule roles
                    $userRoles = auth()->user()->roles->pluck('name')->toArray();
                    $ruleRoles = $matchingRule->approver_role ?? [];
                    $commonRoles = array_intersect($userRoles, $ruleRoles);
                    $recordedRole = reset($commonRoles);
                }

                $record->addSignature(auth()->user(), $matchingRule->signature_type, $recordedRole);

                // Notify next approvers
                $service->notifyNextApprovers($record);

                Notification::make()
                    ->title('Document Successfully Signed')
                    ->success()
                    ->send();

                if ($record->isFullyApproved()) {
                    $record->update(['status' => ProfitabilityAnalysisStatus::Approved]);
                }
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                $isMarginApproved = $record?->is_margin_approved ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->is_margin_approved : false);

                // Check for approved proposal. Using relationship check which is now HasOne
                $proposal = $record?->proposal;
                $proposalStatus = $proposal?->status;
                if ($proposalStatus instanceof \BackedEnum) {
                    $proposalStatus = $proposalStatus->value;
                }

                $allowedPAStatuses = [
                    ProfitabilityAnalysisStatus::Submitted->value,
                    'submitted',
                ];

                return in_array($status, $allowedPAStatuses, true)
                    && $isMarginApproved
                    && ($proposalStatus === ProposalStatus::Approved->value || $proposalStatus === 'approved');
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
                    ->placeholder('Example: Proposal '.($record?->proposal?->proposal_number ?? '...'))
                    ->default(fn ($record) => $record?->proposal?->proposal_number ?? 'Project for '.$record?->customer?->name),
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

                return $rec && ! $rec->proposal && $rec->is_margin_approved;
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

                DB::transaction(function () use ($record, $data) {
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
                });

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
                        : ProfitabilityAnalysisResource::class;

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
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->update($data);

                    if (isset($data['manpowerItems'])) {
                        $record->manpowerItems()->delete();
                        foreach ($data['manpowerItems'] as $item) {
                            $record->manpowerItems()->create($item);
                        }
                    }
                });

                Notification::make()
                    ->title('Manpower Costing Updated')
                    ->success()
                    ->send();
            })
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
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->update($data);

                    if (isset($data['operationalItems'])) {
                        $record->operationalItems()->delete();
                        foreach ($data['operationalItems'] as $item) {
                            $record->operationalItems()->create($item);
                        }
                    }
                });

                Notification::make()
                    ->title('Operational Costing Updated')
                    ->success()
                    ->send();
            })
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
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                $record->update($data);

                Notification::make()
                    ->title('Manual Costs Updated')
                    ->success()
                    ->send();
            })
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
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function (array $data, ProfitabilityAnalysis $record): void {
                $record->update($data);

                Notification::make()
                    ->title('Indirect Costs updated')
                    ->success()
                    ->send();
            })
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
            $this->getIncompleteSubmitWarningAction(),
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
