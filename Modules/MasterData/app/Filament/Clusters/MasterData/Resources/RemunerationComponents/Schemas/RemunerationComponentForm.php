<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\RemunerationCategory;

class RemunerationComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->options(RemunerationCategory::class)
                ->required(),
            Toggle::make('is_fixed')
                ->label('Is Fixed Allowance?')
                ->default(true),
        ];
    }
}
