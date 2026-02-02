<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemeForm
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
                ->unique(WorkScheme::class, 'code', ignoreRecord: true)
                ->placeholder('WS001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Head Office Scheme'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
