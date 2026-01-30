<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\ProposalStatus;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;

class ProfitabilityAnalysesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Document No.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workScheme.name')
                    ->label('Scheme')
                    ->sortable(),
                TextColumn::make('revenue_per_month')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('margin_percentage')
                    ->label('Margin')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn (float $state): string => $state < 10 ? 'danger' : ($state < 20 ? 'warning' : 'success')),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'converted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'converted' => 'Converted',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->slideOver(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->modalFooterActions([
                        Action::make('Sign')
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
                                $userRole = auth()->user()->roles->first()?->name;

                                $matchingRule = $required->firstWhere('approver_role', $userRole);

                                if (! $matchingRule) {
                                    Notification::make()
                                        ->title('Akses Ditolak')
                                        ->body('Peran Anda tidak diperlukan untuk menandatangani dokumen ini pada tahap ini.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                if ($record->hasSignatureFrom($userRole)) {
                                    Notification::make()
                                        ->title('Sudah Ditandatangani')
                                        ->body('Anda sudah menandatangani dokumen ini.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                                $qrCode = $service->generateQRCode($qrData);

                                $record->addSignature(auth()->user(), $matchingRule->signature_type, $qrCode);

                                Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => 'approved']);
                                }
                            })
                            ->visible(fn (ProfitabilityAnalysis $record) => in_array($record->status, ['submitted', 'draft'])),

                        Action::make('Submit')
                            ->color('info')
                            ->icon('heroicon-o-paper-airplane')
                            ->requiresConfirmation()
                            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'submitted']))
                            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'draft'),

                        Action::make('Approve')
                            ->color('success')
                            ->icon('heroicon-o-check')
                            ->requiresConfirmation()
                            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'approved']))
                            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'submitted'),

                        Action::make('Reject')
                            ->color('danger')
                            ->icon('heroicon-o-x-mark')
                            ->requiresConfirmation()
                            ->action(fn (ProfitabilityAnalysis $record) => $record->update(['status' => 'rejected']))
                            ->visible(fn (ProfitabilityAnalysis $record) => $record->status === 'submitted'),
                    ]),
                EditAction::make()
                    ->slideOver(),
                Action::make('generateProject')
                    ->label('Generate Project')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->project()->exists() &&
                        $record->status === 'approved' &&
                        $record->revenue_per_month !== null &&
                        $record->margin_percentage !== null &&
                        (! empty($record->analysis_details) || $record->items()->exists())
                    )
                    ->schema([
                        TextInput::make('summary')
                            ->label('Summary')
                            ->default(fn ($record) => "You are about to generate a Project for '{$record->customer?->name}'. This will consume the next sequence number for this customer and work scheme.")
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('project_name_override')
                            ->label('Project Name (Optional)')
                            ->placeholder(fn ($record) => $record->proposal?->proposal_number ?? 'Project for '.$record->customer?->name),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(ProjectGenerationService::class);

                        // We could pass the override name to the service if needed
                        $project = $service->generateFromPA($record);

                        if (! empty($data['project_name_override'])) {
                            $project->update(['name' => $data['project_name_override']]);
                        }

                        Notification::make()
                            ->title('Project Generated')
                            ->body("Project Code: {$project->code}")
                            ->success()
                            ->send();
                    }),
                Action::make('createProposal')
                    ->label('Create Proposal')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->visible(fn (ProfitabilityAnalysis $record) => ! $record->proposal_id && $record->status === 'approved')
                    ->schema([
                        TextInput::make('proposal_number')
                            ->required()
                            ->unique('proposals', 'proposal_number'),
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
                        $proposal = \Modules\CRM\Models\Proposal::create([
                            'customer_id' => $record->customer_id,
                            'profitability_analysis_id' => $record->id,
                            'work_scheme_id' => $record->work_scheme_id,
                            'proposal_number' => $data['proposal_number'],
                            'amount' => $data['amount'],
                            'submission_date' => $data['submission_date'],
                            'status' => ProposalStatus::Draft,
                        ]);

                        $record->update(['proposal_id' => $proposal->id]);

                        Notification::make()
                            ->title('Proposal Created')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
