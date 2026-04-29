<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxSchemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tax Scheme Definition')
                    ->description('Define standard tax schemes and rates used for financial accounting.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Scheme Name')
                            ->placeholder('e.g. PPh 21 Ter A, PPN Standar')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the tax scheme.'),
                        TextInput::make('code')
                            ->label('Scheme Code')
                            ->placeholder('e.g. TAX-PPH21A, TAX-PPN')
                            ->required()
                            ->unique(\Modules\MasterData\Models\TaxScheme::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this tax scheme.'),
                        TextInput::make('scheme_code')
                            ->label('Scheme Identifier')
                            ->placeholder('e.g. skema_1, skema_2a')
                            ->required()
                            ->helperText('Internal identifier used for calculation logic mapping.'),
                        TextInput::make('rate_percentage')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->placeholder('0.00')
                            ->default(0)
                            ->required()
                            ->helperText('The percentage rate to be applied for this scheme.'),
                        Textarea::make('notes')
                            ->label('Scheme Details')
                            ->placeholder('Enter specific conditions or legal references...')
                            ->helperText('Optional contextual information regarding this tax scheme.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this tax scheme.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this tax scheme can be assigned to tax objects.'),
                        Toggle::make('is_default')
                            ->label('Default Scheme')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new tax configurations.'),
                    ])->columns(2),
            ]);
    }
}
