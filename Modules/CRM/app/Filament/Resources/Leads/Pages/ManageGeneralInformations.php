<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Helper\Filament;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Filament\Actions;
// use Filament\Tables\Actions as TableActions;
use Filament\Schemas\Schema;
use Modules\CRM\Models\GeneralInformation;
use Filament\Notifications;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

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
                        return [
                            'customer_id' => $record->customer_id,
                            'description' => $record->description,
                            'scope_of_work' => $record->title,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;
                        return $data;
                    }),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
