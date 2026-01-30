<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationTable
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
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pic_customer_name')
                    ->label('PIC Customer')
                    ->toggleable(),
                TextColumn::make('rr_submission_id')
                    ->label('RR ID')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (GeneralInformation $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$record->customer->name}.pdf");
                    }),
                EditAction::make(),
                Action::make('Approve')
                    ->label('Approve & Sign')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('pin')
                            ->label('Signature PIN')
                            ->password()
                            ->required()
                            ->helperText('Masukkan PIN tanda tangan digital Anda.'),
                    ])
                    ->action(function (GeneralInformation $record, array $data) {
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
                                    Action::make('profile')
                                        ->label('Ke Profil')
                                        ->button()
                                        ->url(\App\Filament\Pages\EditProfile::getUrl()),
                                ])
                                ->send();

                            return;
                        }

                        $service = app(\Modules\MasterData\Services\SignatureService::class);

                        if (! $service->verifyPin($user, $data['pin'])) {
                            Notification::make()
                                ->title('PIN Salah')
                                ->danger()
                                ->send();

                            return;
                        }

                        $qrData = $service->createSignatureData($user, $record, 'approved');
                        $qrCode = $service->generateQRCode($qrData);

                        $record->addSignature($user, 'approved', $qrCode);
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Berhasil Disetujui')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (GeneralInformation $record) => $record->status !== 'approved'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
