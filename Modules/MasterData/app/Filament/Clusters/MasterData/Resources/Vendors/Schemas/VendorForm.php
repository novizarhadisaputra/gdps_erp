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
            Section::make('Company Information')
                ->description('Basic identification and tax details for the vendor.')
                ->schema([
                    TextInput::make('name')
                        ->label('Vendor Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. PT. Supplier Abadi')
                        ->helperText('The full legal name of the vendor company.'),
                    TextInput::make('tax_id')
                        ->label('NPWP / Tax ID')
                        ->maxLength(255)
                        ->placeholder('00.000.000.0-000.000')
                        ->helperText('The Indonesian Tax Identification Number (NPWP).'),
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('sales@vendor.com')
                        ->helperText('Primary contact email for procurement and invoicing.'),
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(255)
                        ->placeholder('+62... ')
                        ->helperText('Main telephone or mobile contact number.'),
                ])->columns(2),

            Section::make('Financial & Operational Settings')
                ->description('Configure how the system handles transactions with this vendor.')
                ->schema([
                    Select::make('payment_term_id')
                        ->relationship('paymentTerm', 'name')
                        ->label('Payment Term')
                        ->placeholder('Select payment term')
                        ->helperText('Default credit period for vendor invoices.')
                        ->searchable()
                        ->preload()
                        ->createOptionForm(PaymentTermForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver()),
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Whether this vendor is currently available for purchase orders.'),
                ])->columns(2),

            Section::make('Location Details')
                ->description('Physical office or warehouse address.')
                ->schema([
                    Textarea::make('address')
                        ->label('Office Address')
                        ->placeholder('Enter full office address...')
                        ->helperText('The primary location for billing and legal correspondence.')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
