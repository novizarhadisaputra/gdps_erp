<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Schema;
use Modules\MasterData\Models\PaymentTerm;
use Illuminate\Database\Eloquent\Builder;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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
                    ->label('Payment Term')
                    ->options(PaymentTerm::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->default(true),
                Textarea::make('address')
                    ->columnSpanFull(),
            ]);
    }
}
