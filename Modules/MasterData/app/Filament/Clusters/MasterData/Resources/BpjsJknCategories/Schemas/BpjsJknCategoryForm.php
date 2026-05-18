<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\BpjsJknCategory;

class BpjsJknCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('BPJS JKN Category Definition')
                    ->description('Define standard BPJS Health / JKN participation categories.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('e.g. Pekerja Penerima Upah (PPU)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the JKN participation category.'),
                        TextInput::make('code')
                            ->label('Category Code')
                            ->placeholder('e.g. PPU, PBPU, PBI')
                            ->required()
                            ->unique(BpjsJknCategory::class, 'code', ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Unique short code identifier (e.g., PPU, PBPU, PBI).'),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Detailed explanation of this JKN participation category...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->helperText('Brief description or regulatory context for this category.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this category.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this category can be selected in manpower templates.'),
                        Toggle::make('is_default')
                            ->label('Default Category')
                            ->default(false)
                            ->helperText('Sets this as the default JKN category for new setups.'),
                    ])->columns(2),
            ]);
    }
}
