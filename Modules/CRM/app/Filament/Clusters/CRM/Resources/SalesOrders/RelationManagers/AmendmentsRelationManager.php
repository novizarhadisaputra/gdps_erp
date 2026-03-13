<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;

class AmendmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'amendments';

    protected static ?string $recordTitleAttribute = 'amendment_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amendment_number')
                    ->required()
                    ->maxLength(255),
                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(SalesOrderAmendmentStatus::class)
                    ->required(),
                KeyValue::make('snapshot')
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amendment_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(50),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
