<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Modules\CRM\Enums\ProposalStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions;
// use Filament\Tables\Actions as TableActions;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'proposals';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Proposals';

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
            'closed_lost'
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('proposal_number')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->options(ProposalStatus::class)
                    ->required()
                    ->default('draft'),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('IDR'),
                DatePicker::make('submission_date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('proposal_number')
            ->columns([
                TextColumn::make('proposal_number'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('amount')
                    ->money('IDR'),
                TextColumn::make('submission_date')
                    ->date(),
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
                            'amount' => $record->estimated_amount,
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
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
