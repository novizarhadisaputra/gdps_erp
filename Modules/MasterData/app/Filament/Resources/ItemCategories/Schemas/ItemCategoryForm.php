<?php

namespace Modules\MasterData\Filament\Resources\ItemCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('asset_group_id')
                ->relationship('assetGroup', 'name')
                ->searchable()
                ->preload()
                ->label('Asset Group')
                ->placeholder('Select Asset Group for Depreciation')
                ->columnSpanFull(),
            TextInput::make('code')
                ->label('Category Code')
                ->unique(ignoreRecord: true)
                ->maxLength(10)
                ->placeholder('Leave empty for auto-generate'),
            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('Electronics'),
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }
}
