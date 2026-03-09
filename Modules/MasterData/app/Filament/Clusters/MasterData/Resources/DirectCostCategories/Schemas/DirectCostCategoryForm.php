<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
            Section::make('Category Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Category Code')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->hiddenOn('create')
                        ->maxLength(50),
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->options(fn () => \Modules\MasterData\Models\DirectCostCategory::query()
                            ->when($type, fn ($q) => $q->where('type', $type))
                            ->whereNull('parent_id')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('None (Root Category)'),
                    Select::make('type')
                        ->options([
                            'direct' => 'Direct Cost',
                            'indirect' => 'Indirect Cost',
                        ])
                        ->required()
                        ->default($type ?? 'direct')
                        ->disabled((bool) $type)
                        ->dehydrated(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }
}
