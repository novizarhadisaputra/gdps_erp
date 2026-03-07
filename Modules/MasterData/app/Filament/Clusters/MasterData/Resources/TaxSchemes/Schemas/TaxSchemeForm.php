<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Schemas;

use Filament\Schemas\Schema;

class TaxSchemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('scheme_code')
                            ->label('Scheme Code')
                            ->placeholder('Enter Scheme Code...')
                            ->helperText('Brief and clear Scheme Code for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('rate_percentage')
                            ->label('Rate Percentage')
                            ->placeholder('Enter Rate Percentage...')
                            ->helperText('Brief and clear Rate Percentage for this record.')
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->label('Notes')
                            ->placeholder('Enter detailed Notes...')
                            ->helperText('Optional extra information about this record.'),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
