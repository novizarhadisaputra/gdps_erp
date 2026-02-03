<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;

class ManageProfitabilityAnalyses extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'profitabilityAnalyses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $title = 'Profitability Analyses';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'proposal',
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_number')
            ->columns([
                Tables\Columns\TextColumn::make('document_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('margin_percentage')
                    ->suffix('%')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('net_profit')
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->schema(fn (Schema $schema) => ProfitabilityAnalysisForm::configure($schema))
                    ->disabled(fn (ManageProfitabilityAnalyses $livewire) => ! $livewire->getOwnerRecord()->generalInformations()->where('status', 'approved')->exists())
                    ->tooltip(fn (ManageProfitabilityAnalyses $livewire) => ! $livewire->getOwnerRecord()->generalInformations()->where('status', 'approved')->exists() ? 'Requires Approved Risk Register (General Information)' : null)
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        $approvedGi = $record->generalInformations()->where('status', 'approved')->first();

                        return [
                            'customer_id' => $record->customer_id,
                            'work_scheme_id' => $record->work_scheme_id,
                            'general_information_id' => $approvedGi?->id,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $record = $this->getOwnerRecord();
                        $data['customer_id'] = $record->customer_id;
                        $data['work_scheme_id'] = $record->work_scheme_id;
                        $data['general_information_id'] = $record->generalInformations()->where('status', 'approved')->first()?->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                 Actions\ViewAction::make()
                    ->schema(fn (Schema $schema) => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist::configure($schema))
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
                            ->action(function (\Modules\Finance\Models\ProfitabilityAnalysis $record, array $data) {
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

                                $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                                $qrCode = $service->generateQRCode($qrData);

                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                \Filament\Notifications\Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                if ($record->isFullyApproved()) {
                                    $record->update(['status' => 'approved']);
                                }
                            })
                            ->visible(fn (\Modules\Finance\Models\ProfitabilityAnalysis $record) => $record->status !== 'approved'),
                    ]),
                Actions\EditAction::make()
                     ->schema(fn (Schema $schema) => ProfitabilityAnalysisForm::configure($schema)),
                Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
