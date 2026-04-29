<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\WorkEquipment;

class WorkEquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Equipment Definition')
                ->description('Define uniforms, tools, or other work-related equipment.')
                ->schema([
                    TextInput::make('name')
                        ->label('Equipment Name')
                        ->placeholder('e.g. Uniform Set, Security Boots, ID Card')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Equipment Code')
                        ->placeholder('e.g. EQ-UNI, EQ-ID')
                        ->unique(WorkEquipment::class, 'code', ignoreRecord: true)
                        ->helperText('Unique identifier for this equipment type.'),
                ])->columns(2),

            Section::make('Costing & Lifecycle')
                ->description('Specify the acquisition cost and how often the equipment should be replaced.')
                ->schema([
                    TextInput::make('base_cost')
                        ->label('Unit Cost')
                        ->numeric()
                        ->prefix('IDR')
                        ->placeholder('0.00')
                        ->default(0)
                        ->helperText('Standard cost to procure one unit of this equipment.'),
                    TextInput::make('replacement_duration')
                        ->label('Replacement Interval (Months)')
                        ->numeric()
                        ->placeholder('e.g. 6, 12')
                        ->helperText('How often this equipment is re-issued to personnel (e.g. 6 months for uniforms).'),
                ])->columns(2),

            Section::make('Additional Details')
                ->description('Provide extra context and manage availability.')
                ->schema([
                    Textarea::make('description')
                        ->label('Specifications')
                        ->placeholder('Detail the material, brand, or specific requirements...')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive equipment cannot be selected in costing templates.'),
                    Toggle::make('is_default')
                        ->label('Default Equipment')
                        ->default(false)
                        ->helperText('Enable to include this as a standard item in new project costing.'),
                ])->columns(2),
        ];
    }
}
