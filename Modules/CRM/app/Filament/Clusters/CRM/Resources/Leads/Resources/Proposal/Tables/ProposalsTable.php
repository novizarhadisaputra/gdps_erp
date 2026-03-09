<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Exports\ProposalExport;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalForm;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
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
                Action::make('convertToContract')
                    ->label('Convert to Contract')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->visible(fn (Proposal $record): bool => $record->status === ProposalStatus::Approved && ! $record->contracts()->exists())
                    ->requiresConfirmation()
                    ->action(function (Proposal $record) {
                        $contract = Contract::create([
                            'customer_id' => $record->customer_id,
                            'lead_id' => $record->lead_id,
                            'proposal_id' => $record->id,
                            'contract_number' => 'CONTRACT-'.$record->proposal_number,
                            'status' => ContractStatus::Draft,
                        ]);

                        $record->update(['status' => ProposalStatus::Converted]);

                        Notification::make()
                            ->title('Converted to Contract')
                            ->success()
                            ->send();

                        return redirect(ContractResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $contract->id]));
                    }),
                Action::make('resetToApproved')
                    ->label('Reset to Approved')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Proposal $record): bool => in_array($record->status, [ProposalStatus::Converted, ProposalStatus::Rejected]))
                    ->action(fn (Proposal $record) => $record->update(['status' => ProposalStatus::Approved])),
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Proposal $record) {
                            if ($record->is_manual && $media = $record->getFirstMedia('final_proposal')) {
                                return $media;
                            }

                            $pdf = Pdf::loadView('crm::pdf.proposal', ['record' => $record]);
                            $filename = str_replace(['/', '\\'], '-', $record->proposal_number);

                            return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$filename}.pdf");
                        }),

                    Action::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-table-cells')
                        ->action(function (Proposal $record) {
                            $filename = str_replace(['/', '\\'], '-', $record->proposal_number);

                            return Excel::download(
                                new ProposalExport($record),
                                "proposal-{$filename}.xlsx"
                            );
                        }),

                    Action::make('export_contract')
                        ->label('Export Contract')
                        ->icon('heroicon-o-document-duplicate')
                        ->visible(fn (Proposal $record) => $record->contracts()->exists())
                        ->action(function (Proposal $record) {
                            $contract = $record->contracts()->latest()->first();
                            $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $contract]);

                            $filename = str_replace(['/', '\\'], '-', $contract->contract_number);

                            return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$filename}.pdf");
                        }),

                    Action::make('export_general_information')
                        ->label('Export General Info')
                        ->icon('heroicon-o-information-circle')
                        ->visible(fn (Proposal $record) => $record->lead?->generalInformations()->exists())
                        ->action(function (Proposal $record) {
                            $gi = $record->lead->generalInformations()->latest()->first();
                            $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $gi]);

                            return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$gi->customer->name}.pdf");
                        }),
                ])
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),

                ViewAction::make()
                    ->modalFooterActions([
                        Action::make('Sign')
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

                        Action::make('Reject')
                            ->color('danger')
                            ->icon('heroicon-o-x-mark')
                            ->requiresConfirmation()
                            ->action(fn (Proposal $record) => $record->update(['status' => ProposalStatus::Rejected]))
                            ->visible(fn (Proposal $record) => $record->status === ProposalStatus::Submitted),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
