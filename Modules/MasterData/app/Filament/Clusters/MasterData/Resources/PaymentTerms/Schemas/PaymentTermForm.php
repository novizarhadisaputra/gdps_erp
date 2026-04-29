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
            Section::make('Payment Term Details')
                ->description('Configure credit and payment terms for customers and vendors.')
                ->schema([
                    TextInput::make('name')
                        ->label('Term Name')
                        ->placeholder('e.g. Net 30 Days, COD, Due on Receipt')
                        ->helperText('The descriptive name of the payment term.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Term Code')
                        ->placeholder('e.g. NET30, COD')
                        ->helperText('A unique short code identifying the payment term.')
                        ->required()
                        ->unique(PaymentTerm::class, 'code', ignoreRecord: true),
                    TextInput::make('days')
                        ->label('Credit Days')
                        ->numeric()
                        ->required()
                        ->prefix('Days')
                        ->placeholder('30')
                        ->helperText('The number of days allowed for payment.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this payment term.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this term for transactions.'),
                    Toggle::make('is_default')
                        ->label('Default Term')
                        ->default(false)
                        ->helperText('Set as the default payment term for new partners.'),
                ])->columns(2),
        ];
    }
}
