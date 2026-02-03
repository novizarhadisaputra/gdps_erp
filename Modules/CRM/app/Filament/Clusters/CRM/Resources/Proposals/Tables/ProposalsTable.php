<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\ContractResource;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workScheme.name')
                    ->label('Scheme')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(ProposalStatus::class),
            ])
            ->recordActions([
                Action::make('createPA')
                    ->label('Create Profitability Analysis')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('info')
                    ->visible(fn (Proposal $record): bool => in_array($record->status, [ProposalStatus::Approved, ProposalStatus::Converted]))
                    ->form([
                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->label('Select Work Scheme')
                            ->default(fn ($record) => $record->work_scheme_id)
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Proposal $record, array $data) {
                        $existingPa = ProfitabilityAnalysis::where('proposal_id', $record->id)->first();

                        if ($existingPa) {
                            Notification::make()
                                ->title('PA Already Exists')
                                ->body('Redirecting to the existing Profitability Analysis.')
                                ->warning()
                                ->send();

                            return redirect(ProfitabilityAnalysisResource::getUrl('index'));
                        }

                        $pa = ProfitabilityAnalysis::create([
                            'proposal_id' => $record->id,
                            'customer_id' => $record->customer_id,
                            'work_scheme_id' => $data['work_scheme_id'],
                            'status' => 'draft',
                        ]);

                        Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect(ProfitabilityAnalysisResource::getUrl('index'));
                    }),
                Action::make('convertToContract')
                    ->label('Convert to Contract')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->visible(fn (Proposal $record): bool => $record->status === ProposalStatus::Approved || $record->contracts->count() === 0)
                    ->requiresConfirmation()
                    ->action(function (Proposal $record) {
                        $contract = Contract::create([
                            'customer_id' => $record->customer_id,
                            'proposal_id' => $record->id,
                            'contract_number' => 'CONTRACT-'.$record->proposal_number,
                            'status' => ContractStatus::Draft,
                        ]);

                        $record->update(['status' => ProposalStatus::Converted]);

                        Notification::make()
                            ->title('Converted to Contract')
                            ->success()
                            ->send();

                        return redirect(ContractResource::getUrl('index'));
                    }),
                ViewAction::make()
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
                            ->action(function (Proposal $record, array $data) {
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

                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => ProposalStatus::Approved]);
                                }
                            })
                            ->visible(fn (Proposal $record) => in_array($record->status, [ProposalStatus::Submitted, ProposalStatus::Draft])),

                        Action::make('Submit')
                            ->color('info')
                            ->icon('heroicon-o-paper-airplane')
                            ->requiresConfirmation()
                            ->action(fn (Proposal $record) => $record->update(['status' => ProposalStatus::Submitted]))
                            ->visible(fn (Proposal $record) => $record->status === ProposalStatus::Draft),

                        Action::make('Approve')
                            ->color('success')
                            ->icon('heroicon-o-check')
                            ->requiresConfirmation()
                            ->action(fn (Proposal $record) => $record->update(['status' => ProposalStatus::Approved]))
                            ->visible(fn (Proposal $record) => $record->status === ProposalStatus::Submitted),

                        Action::make('Reject')
                            ->color('danger')
                            ->icon('heroicon-o-x-mark')
                            ->requiresConfirmation()
                            ->action(fn (Proposal $record) => $record->update(['status' => ProposalStatus::Rejected]))
                            ->visible(fn (Proposal $record) => $record->status === ProposalStatus::Submitted),

                        \Filament\Actions\ActionGroup::make([
                            Action::make('export_proposal')
                                ->label('Export Proposal')
                                ->icon('heroicon-o-document-text')
                                ->action(function (Proposal $record) {
                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $record]);

                                    return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$record->proposal_number}.pdf");
                                }),

                            Action::make('export_contract')
                                ->label('Export Contract')
                                ->icon('heroicon-o-document-duplicate')
                                ->visible(fn (Proposal $record) => $record->contracts()->exists())
                                ->action(function (Proposal $record) {
                                    $contract = $record->contracts()->latest()->first();
                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.contract', ['record' => $contract]);

                                    return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$contract->contract_number}.pdf");
                                }),

                            Action::make('export_general_information')
                                ->label('Export General Info')
                                ->icon('heroicon-o-information-circle')
                                ->visible(fn (Proposal $record) => $record->lead?->generalInformations()->exists())
                                ->action(function (Proposal $record) {
                                    $gi = $record->lead->generalInformations()->latest()->first();
                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.general_information', ['record' => $gi]);

                                    return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$gi->customer->name}.pdf");
                                }),
                        ])
                        ->label('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->button(),
                    ]),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
