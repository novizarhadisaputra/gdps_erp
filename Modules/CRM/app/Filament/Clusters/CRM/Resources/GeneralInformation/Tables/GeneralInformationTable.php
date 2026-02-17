<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Models\GeneralInformation;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;

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
                        $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$record->customer->name}.pdf");
                    }),
                Action::make('go_no_go')
                    ->label('RR Go/No-Go')
                    ->icon('heroicon-o-check-circle')
                    ->color(fn (GeneralInformation $record) => match ($record->rr_status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->schema([
                        Select::make('rr_status')
                            ->label('Decision')
                            ->options([
                                'approved' => 'GO (Approved)',
                                'rejected' => 'NO-GO (Rejected)',
                                'in_progress' => 'IN PROGRESS',
                            ])
                            ->required(),
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3),
                    ])
                    ->action(function (GeneralInformation $record, array $data) {
                        $record->update([
                            'rr_status' => $data['rr_status'],
                            'remarks' => $data['remarks'],
                        ]);

                        Notification::make()
                            ->title('Decision Recorded')
                            ->success()
                            ->send();

                        if ($data['rr_status'] === 'approved' && $record->isFullyApproved()) {
                            $record->update(['status' => 'approved']);
                        }
                    })
                    ->visible(fn (GeneralInformation $record) => $record->status !== 'approved'),
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

                                if ($record->hasSignatureFrom(auth()->user()->roles->first()?->name)) {
                                    Notification::make()
                                        ->title('Anda sudah menandatangani dokumen ini')
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
                                    $record->update(['status' => 'approved']);
                                }
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status !== 'approved'),
                        Action::make('create_pa_modal')
                            ->label('Create PA')
                            ->icon('heroicon-o-plus')
                            ->color('success')
                            ->fillForm(fn (GeneralInformation $record) => [
                                'general_information_id' => $record->id,
                                'customer_id' => $record->customer_id,
                                'work_scheme_id' => $record->lead?->work_scheme_id,
                            ])
                            ->schema(ProfitabilityAnalysisForm::schema())
                            ->action(function (GeneralInformation $record, array $data) {
                                $data['lead_id'] = $record->lead_id;
                                $data['general_information_id'] = $record->id;
                                $data['status'] = 'draft';

                                ProfitabilityAnalysis::create($data);

                                Notification::make()
                                    ->title('Profitability Analysis Created')
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status === 'approved' && $record->profitabilityAnalyses()->doesntExist()),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationForm::configure($schema)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
