<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\Contract;
use Modules\MasterData\Services\SignatureService;

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
                TextColumn::make('contract_number'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('expiry_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
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
                ViewAction::make()
                    ->schema(fn (Schema $schema) => ContractInfolist::configure($schema))
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
                            ->action(function (Contract $record, array $data) {
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

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => ContractStatus::Active]);
                                }
                            })
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Draft),

                        Action::make('Activate')
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->requiresConfirmation()
                            ->action(fn (Contract $record) => $record->update(['status' => ContractStatus::Active]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Draft),

                        Action::make('Terminate')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->requiresConfirmation()
                            ->form([
                                Textarea::make('termination_reason')
                                    ->label('Reason for Termination')
                                    ->required(),
                            ])
                            ->action(fn (Contract $record, array $data) => $record->update([
                                'status' => ContractStatus::Terminated,
                                'termination_reason' => $data['termination_reason'],
                            ]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Active),

                        Action::make('Mark Expired')
                            ->color('warning')
                            ->icon('heroicon-o-clock')
                            ->requiresConfirmation()
                            ->action(fn (Contract $record) => $record->update(['status' => ContractStatus::Expired]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Active),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ContractForm::configure($schema)),
                DeleteAction::make(),
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Contract $record) {
                        $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$record->contract_number}.pdf");
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
