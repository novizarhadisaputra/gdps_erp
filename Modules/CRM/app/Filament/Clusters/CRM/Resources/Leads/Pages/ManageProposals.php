<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas\ProposalForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas\ProposalInfolist;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'proposals';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Proposals';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'approach',
            'proposal',
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('proposal_number')
            ->columns([
                TextColumn::make('proposal_number'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('amount')
                    ->money('IDR'),
                TextColumn::make('submission_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => ProposalForm::configure($schema))
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();

                        return [
                            'customer_id' => $record->customer_id,
                            'work_scheme_id' => $record->work_scheme_id,
                            'amount' => $record->estimated_amount,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema(fn (Schema $schema) => ProposalInfolist::configure($schema))
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
                            // Check compatibility with Enum status
                            ->visible(fn (Proposal $record) => ! in_array($record->status, [ProposalStatus::Approved, ProposalStatus::Converted, ProposalStatus::Rejected])),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
                DeleteAction::make(),
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

                            return;
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
                    }),
                Action::make('convertToContract')
                    ->label('Convert to Contract')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->visible(fn (Proposal $record): bool => ($record->status === ProposalStatus::Approved || $record->status === 'approved') && $record->contracts->count() === 0)
                    ->requiresConfirmation()
                    ->action(function (Proposal $record) {
                        Contract::create([
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
                    }),
                ActionGroup::make([
                    Action::make('export_proposal')
                        ->label('Export Proposal')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Proposal $record) {
                            $pdf = Pdf::loadView('crm::pdf.proposal', ['record' => $record]);

                            return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$record->proposal_number}.pdf");
                        }),

                    Action::make('export_contract')
                        ->label('Export Contract')
                        ->icon('heroicon-o-document-duplicate')
                        ->visible(fn (Proposal $record) => $record->contracts()->exists())
                        ->action(function (Proposal $record) {
                            $contract = $record->contracts()->latest()->first();
                            $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $contract]);

                            return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$contract->contract_number}.pdf");
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
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
