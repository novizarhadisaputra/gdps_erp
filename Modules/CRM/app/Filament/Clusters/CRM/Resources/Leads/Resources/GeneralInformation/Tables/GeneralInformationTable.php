<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Models\GeneralInformation;
use Modules\MasterData\Services\SignatureService;

class GeneralInformationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scope_of_work')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_start_date')
                    ->date()
                    ->label('Start')
                    ->sortable(),
                TextColumn::make('estimated_end_date')
                    ->date()
                    ->label('End')
                    ->sortable(),
                IconColumn::make('tor')
                    ->label('ToR')
                    ->getStateUsing(fn ($record) => $record->hasMedia('tor'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('tor');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfp')
                    ->label('RFP')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfp'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfp');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfi')
                    ->label('RFI')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfi'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfi');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
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
                        $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$record->document_number}.pdf");
                    }),
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
                            ->action(function (GeneralInformation $record, array $data) {
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

                                $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => 'approved']);
                                }
                            })
                            ->visible(fn (GeneralInformation $record) => in_array($record->status, ['submitted', 'draft'])),

                        Action::make('Submit')
                            ->color('info')
                            ->icon('heroicon-o-paper-airplane')
                            ->requiresConfirmation()
                            ->action(fn (GeneralInformation $record) => $record->update(['status' => 'submitted']))
                            ->visible(fn (GeneralInformation $record) => $record->status === 'draft'),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource::form($schema)),
                DeleteAction::make(),
                Action::make('createPA')
                    ->label('Create PA')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Create Profitability Analysis')
                    ->modalDescription('Apakah Anda yakin ingin membuat data Profitability Analysis (PA) berdasarkan General Information ini?')
                    ->action(function ($record) {
                        $record->toProfitabilityAnalysis();

                        Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect()->to(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::getUrl('profitability-analyses', ['record' => $record->lead]));
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
