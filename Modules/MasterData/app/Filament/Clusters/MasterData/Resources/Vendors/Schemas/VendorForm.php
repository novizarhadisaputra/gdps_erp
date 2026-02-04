<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('tax_id')
                    ->label('NPWP / Tax ID')
                    ->maxLength(255),
                Select::make('payment_term_id')
                    ->relationship('paymentTerm', 'name')
                    ->label('Payment Term')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(PaymentTermForm::schema())
                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                Toggle::make('is_active')
                    ->default(true),
                Textarea::make('address')
                    ->columnSpanFull(),
        ];
    }
}
