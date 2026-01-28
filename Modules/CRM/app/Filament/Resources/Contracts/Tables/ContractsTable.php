<?php

namespace Modules\CRM\Filament\Resources\Contracts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
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
                                            \Filament\Notifications\Actions\Action::make('profile')
                                                ->label('Ke Profil')
                                                ->button()
                                                ->url(\App\Filament\Pages\EditProfile::getUrl()),
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
                                            \Filament\Notifications\Actions\Action::make('profile')
                                                ->label('Ke Profil')
                                                ->button()
                                                ->url(\App\Filament\Pages\EditProfile::getUrl()),
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
