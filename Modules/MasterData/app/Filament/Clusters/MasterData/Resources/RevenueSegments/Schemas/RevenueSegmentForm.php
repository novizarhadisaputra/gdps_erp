<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(RevenueSegment::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('code')
                ->required()
                ->unique(RevenueSegment::class, 'code', ignoreRecord: true)
                ->placeholder('RS001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Aviation'),
            Select::make('unit_id')
                ->relationship('unit', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn () => auth()->user()->can('view_all_master_data')),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
