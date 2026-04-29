<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ItemCategories\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            Section::make('Category Information')
                ->description('Specify the basic details and classification for items used in the inventory.')
                ->schema([
                    Select::make('asset_group_id')
                        ->relationship('assetGroup', 'name')
                        ->createOptionForm(AssetGroupForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->searchable()
                        ->preload()
                        ->label('Asset Group')
                        ->placeholder('Select Asset Group for Depreciation')
                        ->helperText('Associates this category with an asset group for financial tracking.')
                        ->columnSpanFull(),
                    TextInput::make('code')
                        ->label('Category Code')
                        ->unique(ItemCategory::class, 'code', ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('Leave empty for auto-generate')
                        ->helperText('Unique short code for this category.'),
                    TextInput::make('name')
                        ->label('Category Name')
                        ->required()
                        ->unique(ItemCategory::class, 'name', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('e.g. Electronics, Stationary')
                        ->helperText('The descriptive name of the item category.'),
                    Textarea::make('description')
                        ->label('Detailed Description')
                        ->placeholder('Provide additional context for this category...')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this item category.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this category for use in the system.'),
                    Toggle::make('is_default')
                        ->label('Default Category')
                        ->default(false)
                        ->helperText('Set as the default category when creating new items.'),
                ])->columns(2),
        ];
    }
}
