<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;

class GeneralInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm(CustomerForm::schema()),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('draft'),
                TextInput::make('pic_customer_name')->label('PIC Customer Name'),
                TextInput::make('pic_customer_phone')->label('PIC Customer Phone')->tel(),
                TextInput::make('pic_finance_name')->label('PIC Finance Name'),
                TextInput::make('pic_finance_phone')->label('PIC Finance Phone')->tel(),
                TextInput::make('pic_finance_email')->label('PIC Finance Email')->email(),

                Section::make('Project Details')
                    ->schema([
                        Textarea::make('scope_of_work')
                            ->columnSpanFull(),
                        TextInput::make('location'),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('estimated_start_date'),
                                DatePicker::make('estimated_end_date'),
                            ]),
                        Textarea::make('manpower_qualifications')
                            ->columnSpanFull(),
                        Textarea::make('work_activities')
                            ->columnSpanFull(),
                        Textarea::make('service_level')
                            ->columnSpanFull(),
                        Textarea::make('billing_requirements')
                            ->columnSpanFull(),
                    ]),

                Repeater::make('risk_management')
                    ->schema([
                        TextInput::make('risk_item')->required(),
                        TextInput::make('mitigation')->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Repeater::make('feasibility_study')
                    ->schema([
                        TextInput::make('item')->required(),
                        TextInput::make('value')->required(),
                        Textarea::make('notes')->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Textarea::make('description')->columnSpanFull()->rows(3),
                Textarea::make('remarks')->columnSpanFull()->rows(2),
                TextInput::make('rr_submission_id')
                    ->label('RR Submission ID')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
