<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Schemas;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ChartOfAccount;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\RevenueType;

class AccountMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mapping Target')
                    ->description('Select the entity this mapping applies to.')
                    ->schema([
                        MorphToSelect::make('mappable')
                            ->label('Entity')
                            ->types([
                                MorphToSelect\Type::make(\Modules\MasterData\Models\ProjectArea::class)
                                    ->titleAttribute('name')
                                    ->label('Project Area'),
                                MorphToSelect\Type::make(\Modules\CRM\Models\Customer::class)
                                    ->titleAttribute('name')
                                    ->label('Customer'),
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Mapping Configuration')
                    ->description('Define the GL account for specific revenue types and segments.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Mapping Type')
                                    ->options([
                                        'accrual' => 'Accrual',
                                        'revenue' => 'Revenue',
                                        'receivable' => 'Receivable',
                                        'unbilled_receivable' => 'Unbilled Receivable',
                                        'expense' => 'Expense',
                                    ])
                                    ->required(),

                                Select::make('chart_of_account_id')
                                    ->label('Chart of Account (GL)')
                                    ->options(ChartOfAccount::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('revenue_type_id')
                                    ->label('Revenue Type')
                                    ->options(RevenueType::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                Select::make('revenue_segment_id')
                                    ->label('Revenue Segment')
                                    ->options(RevenueSegment::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
