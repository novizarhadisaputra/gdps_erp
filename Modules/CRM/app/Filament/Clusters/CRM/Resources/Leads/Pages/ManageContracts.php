<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;

class ManageContracts extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'contracts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $title = 'Contracts';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contract_number')
            ->columns([
                Tables\Columns\TextColumn::make('contract_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('expiry_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->schema(fn (Schema $schema) => ContractForm::configure($schema))
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        // Try to find the latest proposal linked to this lead
                        $latestProposal = $record->proposals()->latest()->first();

                        return [
                            'customer_id' => $record->customer_id,
                            'proposal_id' => $latestProposal?->id,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $record = $this->getOwnerRecord();
                        $data['customer_id'] = $record->customer_id;
                        $data['work_scheme_id'] = $record->work_scheme_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                 Actions\ViewAction::make()
                    ->schema(fn (Schema $schema) => \Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractInfolist::configure($schema))
                    ->modalFooterActions([
                        Actions\Action::make('Sign')
                            ->label('Digital Signature')
                            ->color('primary')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('pin')
                                    ->label('Signature PIN')
                                    ->password()
                                    ->required()
                                    ->helperText('Masukkan PIN tanda tangan digital Anda.'),
                            ])
                            ->action(function (\Modules\CRM\Models\Contract $record, array $data) {
                                $service = app(\Modules\MasterData\Services\SignatureService::class);

                                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('PIN Salah')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $required = $service->getRequiredApprovers($record);
                                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                                if (! $matchingRule) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Akses Ditolak')
                                        ->body('Anda tidak memiliki otoritas untuk menandatangani dokumen ini berdasarkan aturan approval saat ini.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Sudah Ditandatangani')
                                        ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                \Filament\Notifications\Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => \Modules\CRM\Enums\ContractStatus::Active]);
                                }
                            })
                            ->visible(fn (\Modules\CRM\Models\Contract $record) => $record->status === \Modules\CRM\Enums\ContractStatus::Draft),

                        Actions\Action::make('Activate')
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->requiresConfirmation()
                            ->action(fn (\Modules\CRM\Models\Contract $record) => $record->update(['status' => \Modules\CRM\Enums\ContractStatus::Active]))
                            ->visible(fn (\Modules\CRM\Models\Contract $record) => $record->status === \Modules\CRM\Enums\ContractStatus::Draft),

                        Actions\Action::make('Terminate')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->requiresConfirmation()
                            ->form([
                                \Filament\Forms\Components\Textarea::make('termination_reason')
                                    ->label('Reason for Termination')
                                    ->required(),
                            ])
                            ->action(fn (\Modules\CRM\Models\Contract $record, array $data) => $record->update([
                                'status' => \Modules\CRM\Enums\ContractStatus::Terminated,
                                'termination_reason' => $data['termination_reason'],
                            ]))
                            ->visible(fn (\Modules\CRM\Models\Contract $record) => $record->status === \Modules\CRM\Enums\ContractStatus::Active),

                        Actions\Action::make('Mark Expired')
                            ->color('warning')
                            ->icon('heroicon-o-clock')
                            ->requiresConfirmation()
                            ->action(fn (\Modules\CRM\Models\Contract $record) => $record->update(['status' => \Modules\CRM\Enums\ContractStatus::Expired]))
                            ->visible(fn (\Modules\CRM\Models\Contract $record) => $record->status === \Modules\CRM\Enums\ContractStatus::Active),
                    ]),
                Actions\EditAction::make()
                     ->schema(fn (Schema $schema) => ContractForm::configure($schema)),
                Actions\DeleteAction::make(),
                Actions\Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (\Modules\CRM\Models\Contract $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.contract', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$record->contract_number}.pdf");
                    }),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
