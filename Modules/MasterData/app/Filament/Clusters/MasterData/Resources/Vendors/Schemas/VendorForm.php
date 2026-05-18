<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
            Section::make(__('Company Information'))
                ->description(__('Basic identification and tax details for the vendor.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Vendor Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. PT. Supplier Abadi'))
                        ->helperText(__('The full legal name of the vendor company.')),
                    TextInput::make('tax_id')
                        ->label(__('NPWP / Tax ID'))
                        ->maxLength(255)
                        ->placeholder(__('00.000.000.0-000.000'))
                        ->helperText(__('The Indonesian Tax Identification Number (NPWP).')),
                    TextInput::make('email')
                        ->label(__('Email Address'))
                        ->email()
                        ->maxLength(255)
                        ->placeholder(__('sales@vendor.com'))
                        ->helperText(__('Primary contact email for procurement and invoicing.')),
                    TextInput::make('phone')
                        ->label(__('Phone Number'))
                        ->tel()
                        ->maxLength(255)
                        ->placeholder(__('+62... '))
                        ->helperText(__('Main telephone or mobile contact number.')),
                ])->columns(2),

            Section::make(__('Financial & Operational Settings'))
                ->description(__('Configure how the system handles transactions with this vendor.'))
                ->schema([
                    Select::make('payment_term_id')
                        ->relationship('paymentTerm', 'name')
                        ->label(__('Payment Term'))
                        ->placeholder(__('Select payment term'))
                        ->helperText(__('Default credit period for vendor invoices.'))
                        ->searchable()
                        ->preload()
                        ->createOptionForm(PaymentTermForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver()),
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Whether this vendor is currently available for purchase orders.')),
                ])->columns(2),

            Section::make(__('Location Details'))
                ->description(__('Physical office or warehouse address.'))
                ->schema([
                    Textarea::make('address')
                        ->label(__('Office Address'))
                        ->placeholder(__('Enter full office address...'))
                        ->helperText(__('The primary location for billing and legal correspondence.'))
                        ->columnSpanFull(),
                ]),
        ];
    }
}
