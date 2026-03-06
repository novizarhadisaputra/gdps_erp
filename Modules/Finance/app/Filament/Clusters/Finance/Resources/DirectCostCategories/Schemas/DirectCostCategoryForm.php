<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DirectCostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Details')
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('None (Root Category)'),
                        Select::make('type')
                            ->options([
                                'direct' => 'Direct Cost',
                                'indirect' => 'Indirect Cost',
                            ])
                            ->required()
                            ->default('direct'),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
