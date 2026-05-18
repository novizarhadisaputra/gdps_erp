<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\RevenueType;

class RevenueTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(RevenueType::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Revenue Type Definition'))
                ->description(__('Define categories for revenue streams to enable granular financial mapping and analysis.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Type Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. Manpower, Material, Overtime'))
                        ->helperText(__('The descriptive name of the revenue category.')),
                    TextInput::make('code')
                        ->label(__('Type Code'))
                        ->maxLength(50)
                        ->placeholder(__('e.g. MANPOWER, MAT, OVT'))
                        ->helperText(__('A unique code identifier for system-wide mapping.')),
                ])->columns(2),

            Section::make(__('Configuration'))
                ->description(__('Manage the behavior and status of this revenue type.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this type for new project configurations.')),
                    Toggle::make('is_default')
                        ->label(__('Default Type'))
                        ->default(false)
                        ->helperText(__('Automatically select this type when creating new items.')),
                ])->columns(2),
        ];
    }
}
