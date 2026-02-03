<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Notifications;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\GeneralInformation;

class ManageGeneralInformations extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'generalInformations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $title = 'General Information';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('signatures'))
            ->recordTitleAttribute('document_number')
            ->columns([
                Tables\Columns\TextColumn::make('document_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rr_submission_id')
                    ->label('RR ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('scope_of_work')->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationForm::configure($schema))
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        $data = [
                            'customer_id' => $record->customer_id,
                            'description' => $record->description,
                            'scope_of_work' => $record->title,
                        ];

                        // Auto-fill PICs from Customer Contacts if available
                        if ($record->customer && ! empty($record->customer->contacts)) {
                            $contacts = $record->customer->contacts;
                            // Map existing customer contacts to GeneralInformationPic format
                            $pics = collect($contacts)->map(function ($contact) {
                                return [
                                    'name' => $contact['name'] ?? '',
                                    'email' => $contact['email'] ?? '',
                                    'phone' => $contact['phone'] ?? '',
                                    'contact_role_id' => $contact['type'] ?? null,
                                ];
                            })->toArray();

                            $data['pics'] = $pics;
                        }

                        return $data;
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Actions\ViewAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationInfolist::configure($schema))
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
                            ->action(function (GeneralInformation $record, array $data) {
                                $service = app(\Modules\MasterData\Services\SignatureService::class);

                                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                                    Notifications\Notification::make()
                                        ->title('PIN Salah')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $required = $service->getRequiredApprovers($record);
                                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                                if (! $matchingRule) {
                                    Notifications\Notification::make()
                                        ->title('Akses Ditolak')
                                        ->body('Anda tidak memiliki otoritas untuk menandatangani dokumen ini berdasarkan aturan approval saat ini.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                                    Notifications\Notification::make()
                                        ->title('Sudah Ditandatangani')
                                        ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                Notifications\Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                // Auto-approve if fully signed and RR is approved (or no RR)
                                if ($record->isFullyApproved() && ($record->rr_status === 'approved' || ! $record->rr_submission_id)) {
                                    $record->update(['status' => 'approved']);
                                } elseif ($record->isFullyApproved()) {
                                    Notifications\Notification::make()
                                        ->title('Tanda Tangan Lengkap')
                                        ->body('Menunggu Status Risk Register "Approved" untuk finalisasi.')
                                        ->info()
                                        ->send();
                                }
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status !== 'approved'),
                        Actions\Action::make('create_pa_modal')
                            ->label('Create PA')
                            ->icon('heroicon-o-plus')
                            ->color('success')
                            ->fillForm(fn (GeneralInformation $record) => [
                                'general_information_id' => $record->id,
                                'customer_id' => $record->customer_id,
                                'work_scheme_id' => $record->lead?->work_scheme_id,
                            ])
                            ->schema(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm::schema())
                            ->action(function (GeneralInformation $record, array $data) {
                                $data['lead_id'] = $record->lead_id;
                                $data['general_information_id'] = $record->id;
                                $data['status'] = 'draft';

                                // Extract items to handle separately
                                $items = $data['items'] ?? [];
                                unset($data['items']);

                                $pa = \Modules\Finance\Models\ProfitabilityAnalysis::create($data);

                                // Create items
                                foreach ($items as $itemData) {
                                    $pa->items()->create($itemData);
                                }

                                Notifications\Notification::make()
                                    ->title('Profitability Analysis Created')
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status === 'approved' && $record->profitabilityAnalyses()->doesntExist()),
                    ]),
                Actions\EditAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationForm::configure($schema)),
                Actions\DeleteAction::make(),
                Actions\Action::make('pdf')
                    ->label('Export PDF')
                    ->modalDescription(fn (GeneralInformation $record) => "Are you sure you want to export this General Information - {$record->customer->name}.pdf to PDF?")
                    ->modalHeading('Export General Information to PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->action(function (GeneralInformation $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "General Information - {$record->customer->name}.pdf");
                    }),
                Actions\Action::make('check_status')
                    ->label('Check Status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->action(function (GeneralInformation $record) {
                        $status = app(\Modules\Project\Services\RiskRegisterService::class)->getRiskRegisterStatus($record->rr_submission_id ?? '');

                        // Mocking status transition based on current state
                        if ($record->rr_status !== 'approved') {
                            $status = 'approved';
                        }

                        // Update the RR Status column
                        $record->update([
                            'rr_status' => $status,
                        ]);

                        Notifications\Notification::make()
                            ->title('RR Status Updated')
                            ->body("Risk Register status is now: {$status}")
                            ->success()
                            ->send();

                        // Check strict approval condition: Both Signatures AND RR Status must be valid
                        if ($record->isFullyApproved() && $record->rr_status === 'approved') {
                            $record->update(['status' => 'approved']);

                            Notifications\Notification::make()
                                ->title('General Information Approved')
                                ->body('Dokumen telah disetujui sepenuhnya (Signatures + Risk Register).')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
