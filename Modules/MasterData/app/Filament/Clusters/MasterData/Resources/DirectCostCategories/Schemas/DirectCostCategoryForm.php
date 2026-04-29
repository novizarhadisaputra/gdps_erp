<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DirectCostCategoryForm
{
    public static function configure(Schema $schema, ?string $type = null): Schema
    {
        return $schema
            ->components(static::schema($type));
    }

    public static function schema(?string $type = null): array
    {
        return [
            Section::make('Category Information')
                ->description('Define the classification for direct and indirect costs used in project budgeting.')
                ->schema([
                    TextInput::make('name')
                        ->label('Category Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Consumables, Uniforms')
                        ->helperText('The descriptive name of the cost category.'),
                    TextInput::make('code')
                        ->label('Category Code')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->hiddenOn('create')
                        ->maxLength(50)
                        ->helperText('The unique identifier for this category.'),
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->options(fn () => \Modules\MasterData\Models\DirectCostCategory::query()
                            ->when($type, fn ($q) => $q->where('type', $type))
                            ->whereNull('parent_id')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('None (Root Category)')
                        ->helperText('Select a parent category if this is a sub-category.'),
                    Select::make('type')
                        ->label('Category Type')
                        ->options([
                            'direct' => 'Direct Cost',
                            'indirect' => 'Indirect Cost',
                        ])
                        ->required()
                        ->default($type ?? 'direct')
                        ->disabled((bool) $type)
                        ->dehydrated()
                        ->placeholder('Select type')
                        ->helperText('Categorize whether this is directly or indirectly related to project delivery.'),
                    Textarea::make('description')
                        ->label('Detailed Description')
                        ->placeholder('Provide additional context for this category...')
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this cost category.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this category for use in the system.'),
                    Toggle::make('is_default')
                        ->label('Default Category')
                        ->default(false)
                        ->helperText('Set as the default category when creating new cost entries.'),
                ])->columns(2),
        ];
    }
}
