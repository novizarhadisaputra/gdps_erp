<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Tables;

use App\Filament\Pages\EditProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractForm;
use Modules\CRM\Models\Contract;
use Modules\MasterData\Services\SignatureService;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reminder_status')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', $state)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Contract $record) {
                        $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$record->contract_number}.pdf");
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
                            ->action(function (Contract $record, array $data) {
                                $user = auth()->user();

                                if (! $user->signature_pin) {
                                    Notification::make()
                                        ->title('PIN Belum Diatur')
                                        ->body('Anda belum mengatur PIN tanda tangan. Mohon atur di profil Anda.')
                                        ->danger()
                                        ->actions([
                                            Action::make('profile')
                                                ->label('Ke Profil')
                                                ->button()
                                                ->url(EditProfile::getUrl()),
                                        ])
                                        ->send();

                                    return;
                                }

                                if (! $user->hasMedia('signature')) {
                                    Notification::make()
                                        ->title('Tanda Tangan Belum Diupload')
                                        ->body('Anda belum mengupload gambar tanda tangan. Mohon upload di profil Anda.')
                                        ->danger()
                                        ->actions([
                                            Action::make('profile')
                                                ->label('Ke Profil')
                                                ->button()
                                                ->url(EditProfile::getUrl()),
                                        ])
                                        ->send();

                                    return;
                                }

                                $service = app(SignatureService::class);

                                if (! $service->verifyPin($user, $data['pin'])) {
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

                                // Check if user has already signed
                                // Note: We should fetch role from matching rule?
                                // Or check if we have signature from matching rule role?
                                // Let's use hasSignatureFrom(string|array) which I updated in the Trait.
                                if ($record->hasSignatureFrom($matchingRule->approver_role ?? [])) {
                                    // Wait, if approver_role is array, hasSignatureFrom checks if ANY role signed.
                                    // But User might have multiple roles.
                                    // Robust check: Check if THIS user has signed?
                                    // The trait hasSignatureFrom checks role.
                                    // Better check: $record->signatures()->where('user_id', auth()->id())->exists()
                                    // But user might sign as different capacity?
                                    // For now, let's trust standard flow. If I signed, I signed.
                                    // But hasSignatureFrom checks ROLE.
                                }

                                // Actually, checking if "I have signed" is safer by user ID?
                                // But business logic is "Role has signed".
                                // If I am Manager A, and Manager B (same role) signed, do I need to sign?
                                // If Rule says "Role: Manager", then ONE signature is enough.
                                // So hasSignatureFrom($matchingRule->approver_role) is correct.

                                if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) { // Fallback type not ideal but usually Role
                                    Notification::make()
                                        ->title('Sudah Ditandatangani')
                                        ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
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
                                    $record->update(['status' => ContractStatus::Active]);
                                }
                            })
                            ->visible(fn (Contract $record) => in_array($record->status, [ContractStatus::Draft])),

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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
