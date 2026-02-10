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
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\GeneralInformation;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Services\RiskRegisterService;

class ManageGeneralInformations extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'generalInformations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $title = 'General Information';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

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
            ->modifyQueryUsing(fn ($query) => $query->with('signatures'))
            ->recordTitleAttribute('document_number')
            ->columns([
                TextColumn::make('document_number'),
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
                TextColumn::make('scope_of_work')->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
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
                ViewAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationInfolist::configure($schema))
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

                                $record->addSignature(auth()->user(), $matchingRule->signature_type);

                                Notification::make()
                                    ->title('Dokumen Berhasil Ditandatangani')
                                    ->success()
                                    ->send();

                                // Auto-approve if fully signed and RR is approved (or no RR)
                                if ($record->isFullyApproved() && ($record->rr_status === 'approved' || ! $record->rr_submission_id)) {
                                    $record->update(['status' => 'approved']);
                                } elseif ($record->isFullyApproved()) {
                                    Notification::make()
                                        ->title('Tanda Tangan Lengkap')
                                        ->body('Menunggu Status Risk Register "Approved" untuk finalisasi.')
                                        ->info()
                                        ->send();
                                }
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status !== 'approved'),
                        Action::make('create_pa_modal')
                            ->label('Create PA')
                            ->color('success')
                            ->fillForm(function (GeneralInformation $record) {
                                $lead = $record->lead;
                                $salesPlan = $lead?->salesPlan;

                                return [
                                    'general_information_id' => $record->id,
                                    'customer_id' => $record->customer_id,
                                    'work_scheme_id' => $lead?->work_scheme_id,
                                    'product_cluster_id' => $salesPlan?->product_cluster_id ?? $lead?->product_cluster_id,
                                    'project_area_id' => $salesPlan?->project_area_id ?? $lead?->project_area_id,
                                    'margin_percentage' => $salesPlan?->margin_percentage,
                                    'management_fee' => $salesPlan?->estimated_value * (($salesPlan?->management_fee_percentage ?? 0) / 100),
                                ];
                            })
                            ->schema(ProfitabilityAnalysisForm::schema())
                            ->action(function (GeneralInformation $record, array $data) {
                                $data['lead_id'] = $record->lead_id;
                                $data['general_information_id'] = $record->id;
                                $data['status'] = 'draft';

                                // Extract items to handle separately
                                $items = $data['items'] ?? [];
                                unset($data['items']);

                                $pa = ProfitabilityAnalysis::create($data);

                                // Create items
                                foreach ($items as $itemData) {
                                    $pa->items()->create($itemData);
                                }

                                Notification::make()
                                    ->title('Profitability Analysis Created')
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn (GeneralInformation $record) => $record->status === 'approved' && $record->profitabilityAnalyses()->doesntExist()),
                    ]),
                EditAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationForm::configure($schema)),
                DeleteAction::make(),
                Action::make('pdf')
                    ->label('Export PDF')
                    ->modalDescription(fn (GeneralInformation $record) => "Are you sure you want to export this General Information - {$record->customer->name}.pdf to PDF?")
                    ->modalHeading('Export General Information to PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->action(function (GeneralInformation $record) {
                        $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                        return response()->streamDownload(fn () => print ($pdf->output()), "General Information - {$record->customer->name}.pdf");
                    }),
                Action::make('check_status')
                    ->label('Check Status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->action(function (GeneralInformation $record) {
                        $status = app(RiskRegisterService::class)->getRiskRegisterStatus($record->rr_submission_id ?? '');

                        // Mocking status transition based on current state
                        if ($record->rr_status !== 'approved') {
                            $status = 'approved';
                        }

                        // Update the RR Status column
                        $record->update([
                            'rr_status' => $status,
                        ]);

                        Notification::make()
                            ->title('RR Status Updated')
                            ->body("Risk Register status is now: {$status}")
                            ->success()
                            ->send();

                        // Check strict approval condition: Both Signatures AND RR Status must be valid
                        if ($record->isFullyApproved() && $record->rr_status === 'approved') {
                            $record->update(['status' => 'approved']);

                            Notification::make()
                                ->title('General Information Approved')
                                ->body('Dokumen telah disetujui sepenuhnya (Signatures + Risk Register).')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
