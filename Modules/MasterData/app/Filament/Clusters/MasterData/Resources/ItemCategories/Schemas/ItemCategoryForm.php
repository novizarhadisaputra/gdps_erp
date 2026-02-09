<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ItemCategories\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Schemas\AssetGroupForm;
use Modules\MasterData\Models\ItemCategory;

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
            Section::make('Category Details')
                ->schema([
                    Select::make('asset_group_id')
                        ->relationship('assetGroup', 'name')
                        ->createOptionForm(AssetGroupForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->searchable()
                        ->preload()
                        ->label('Asset Group')
                        ->placeholder('Select Asset Group for Depreciation')
                        ->columnSpanFull(),
                    TextInput::make('code')
                        ->label('Category Code')
                        ->unique(ItemCategory::class, 'code', ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('Leave empty for auto-generate'),
                    TextInput::make('name')
                        ->required()
                        ->unique(ItemCategory::class, 'name', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Electronics'),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
