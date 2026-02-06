<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
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
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
