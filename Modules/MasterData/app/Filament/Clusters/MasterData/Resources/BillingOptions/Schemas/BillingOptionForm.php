<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\BillingOption;

class BillingOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Billing Configuration')
                ->description('Configure the available billing frequency or method options.')
                ->schema([
                    TextInput::make('name')
                        ->label('Billing Option Name')
                        ->placeholder('e.g. Monthly, Quarterly, One-time')
                        ->required()
                        ->maxLength(255)
                        ->helperText('The descriptive name of the billing option.'),
                    TextInput::make('code')
                        ->label('Billing Code')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->unique(BillingOption::class, 'code', ignoreRecord: true)
                        ->helperText('Unique identifier for this billing frequency.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this billing option.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive options will not be visible during contract creation.'),
                    Toggle::make('is_default')
                        ->label('Default Option')
                        ->default(false)
                        ->helperText('Set as the default billing selection for new projects.'),
                ])->columns(2),
        ];
    }
}
