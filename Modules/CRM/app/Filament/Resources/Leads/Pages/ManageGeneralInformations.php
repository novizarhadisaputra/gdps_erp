<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Notifications;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Modules\CRM\Models\GeneralInformation;

class ManageGeneralInformations extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'generalInformations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $title = 'General Information';

    public function form(Schema $schema): Schema
    {
        return GeneralInformationForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_number')
            ->columns([
                Tables\Columns\TextColumn::make('document_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'submitted' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('scope_of_work')->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
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
                Actions\EditAction::make(),
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

                        // Mocking status transition for demo purposes
                        // If current is submitted, change to approved
                        if ($record->status === 'submitted') {
                            $status = 'approved';
                        }

                        $record->update(['status' => strtolower($status)]);

                        Notifications\Notification::make()
                            ->title('Status Updated')
                            ->body("Risk Register status is now: {$status}")
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
