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
            Section::make(__('Category Information'))
                ->description(__('Define the classification for direct and indirect costs used in project budgeting.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Category Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. Consumables, Uniforms'))
                        ->helperText(__('The descriptive name of the cost category.')),
                    TextInput::make('code')
                        ->label(__('Category Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->hiddenOn('create')
                        ->maxLength(50)
                        ->helperText(__('The unique identifier for this category.')),
                    Select::make('parent_id')
                        ->label(__('Parent Category'))
                        ->options(fn () => \Modules\MasterData\Models\DirectCostCategory::query()
                            ->when($type, fn ($q) => $q->where('type', $type))
                            ->whereNull('parent_id')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder(__('None (Root Category)'))
                        ->helperText(__('Select a parent category if this is a sub-category.')),
                    Select::make('type')
                        ->label(__('Category Type'))
                        ->options([
                            'direct' => __('Direct Cost'),
                            'indirect' => __('Indirect Cost'),
                        ])
                        ->required()
                        ->default($type ?? 'direct')
                        ->disabled((bool) $type)
                        ->dehydrated()
                        ->placeholder(__('Select type'))
                        ->helperText(__('Categorize whether this is directly or indirectly related to project delivery.')),
                    Textarea::make('description')
                        ->label(__('Detailed Description'))
                        ->placeholder(__('Provide additional context for this category...'))
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this cost category.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this category for use in the system.')),
                    Toggle::make('is_default')
                        ->label(__('Default Category'))
                        ->default(false)
                        ->helperText(__('Set as the default category when creating new cost entries.')),
                ])->columns(2),
        ];
    }
}
