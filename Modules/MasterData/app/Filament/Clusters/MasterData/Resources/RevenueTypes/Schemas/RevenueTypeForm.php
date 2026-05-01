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
            Section::make('Revenue Type Definition')
                ->description('Define categories for revenue streams to enable granular financial mapping and analysis.')
                ->schema([
                    TextInput::make('name')
                        ->label('Type Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Manpower, Material, Overtime')
                        ->helperText('The descriptive name of the revenue category.'),
                    TextInput::make('code')
                        ->label('Type Code')
                        ->maxLength(50)
                        ->placeholder('e.g. MANPOWER, MAT, OVT')
                        ->helperText('A unique code identifier for system-wide mapping.'),
                ])->columns(2),

            Section::make('Configuration')
                ->description('Manage the behavior and status of this revenue type.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this type for new project configurations.'),
                    Toggle::make('is_default')
                        ->label('Default Type')
                        ->default(false)
                        ->helperText('Automatically select this type when creating new items.'),
                ])->columns(2),
        ];
    }
}
