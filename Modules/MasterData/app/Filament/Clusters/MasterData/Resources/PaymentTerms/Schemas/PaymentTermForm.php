<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\PaymentTerm;

class PaymentTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Payment Term Details'))
                ->description(__('Configure credit and payment terms for customers and vendors.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Term Name'))
                        ->placeholder(__('e.g. Net 30 Days, COD, Due on Receipt'))
                        ->helperText(__('The descriptive name of the payment term.'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('Term Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(PaymentTerm::class, 'code', ignoreRecord: true)
                        ->helperText(__('A unique identification code for this payment term.')),
                    TextInput::make('days')
                        ->label(__('Credit Days'))
                        ->numeric()
                        ->required()
                        ->prefix('Days')
                        ->placeholder(__('30'))
                        ->helperText(__('The number of days allowed for payment.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this payment term.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this term for transactions.')),
                    Toggle::make('is_default')
                        ->label(__('Default Term'))
                        ->default(false)
                        ->helperText(__('Set as the default payment term for new partners.')),
                ])->columns(2),
        ];
    }
}
