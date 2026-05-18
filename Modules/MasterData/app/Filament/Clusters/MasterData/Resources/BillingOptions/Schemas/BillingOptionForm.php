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
            Section::make(__('Billing Configuration'))
                ->description(__('Configure the available billing frequency or method options.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Billing Option Name'))
                        ->placeholder(__('e.g. Monthly, Quarterly, One-time'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('The descriptive name of the billing option.')),
                    TextInput::make('code')
                        ->label(__('Billing Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(BillingOption::class, 'code', ignoreRecord: true)
                        ->helperText(__('Unique identifier for this billing frequency.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this billing option.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Inactive options will not be visible during contract creation.')),
                    Toggle::make('is_default')
                        ->label(__('Default Option'))
                        ->default(false)
                        ->helperText(__('Set as the default billing selection for new projects.')),
                ])->columns(2),
        ];
    }
}
