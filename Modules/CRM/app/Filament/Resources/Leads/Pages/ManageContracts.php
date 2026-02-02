<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
// use Filament\Tables\Actions as TableActions;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Contracts\Schemas\ContractForm;
use Modules\CRM\Filament\Resources\Leads\LeadResource;

class ManageContracts extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'contracts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $title = 'Contracts';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contract_number')
            ->columns([
                Tables\Columns\TextColumn::make('contract_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('expiry_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        // Try to find the latest proposal linked to this lead
                        $latestProposal = $record->proposals()->latest()->first();

                        return [
                            'customer_id' => $record->customer_id,
                            'proposal_id' => $latestProposal?->id,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $record = $this->getOwnerRecord();
                        $data['customer_id'] = $record->customer_id;
                        $data['work_scheme_id'] = $record->work_scheme_id;

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
