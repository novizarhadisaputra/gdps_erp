<?php

namespace Modules\CRM\Filament\Resources\Leads\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Modules\CRM\Enums\ProposalStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ProposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'proposals';

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
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
