<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Filament\Schemas\Schema;

class GeneralInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm(\Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm::schema()),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('draft'),
                \Filament\Forms\Components\TextInput::make('pic_customer_name')->label('PIC Customer Name'),
                \Filament\Forms\Components\TextInput::make('pic_customer_phone')->label('PIC Customer Phone')->tel(),
                \Filament\Forms\Components\TextInput::make('pic_finance_name')->label('PIC Finance Name'),
                \Filament\Forms\Components\TextInput::make('pic_finance_phone')->label('PIC Finance Phone')->tel(),
                \Filament\Forms\Components\TextInput::make('pic_finance_email')->label('PIC Finance Email')->email(),

                \Filament\Forms\Components\Repeater::make('risk_management')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('risk_item')->required(),
                        \Filament\Forms\Components\TextInput::make('mitigation')->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                \Filament\Forms\Components\Repeater::make('feasibility_study')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('item')->required(),
                        \Filament\Forms\Components\TextInput::make('value')->required(),
                        \Filament\Forms\Components\Textarea::make('notes')->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                \Filament\Forms\Components\Textarea::make('description')->columnSpanFull()->rows(3),
                \Filament\Forms\Components\Textarea::make('remarks')->columnSpanFull()->rows(2),
                \Filament\Forms\Components\TextInput::make('rr_submission_id')
                    ->label('RR Submission ID')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
