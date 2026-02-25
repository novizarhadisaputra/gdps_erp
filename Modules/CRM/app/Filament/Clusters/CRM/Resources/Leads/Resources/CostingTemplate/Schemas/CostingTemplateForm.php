<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\CRM\Models\CostingTemplate;

class CostingTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make('General Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('pic_id')
                            ->label('Costing PIC')
                            ->relationship('pic', 'name')
                            ->options(function () {
                                return User::where('id', auth()->id())
                                    ->orWhere('unit_id', '10000016')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                        TextInput::make('description')
                            ->maxLength(255),
                    ]),
                Step::make('Items & Costing')
                    ->schema([
                        ViewField::make('items_notice')
                            ->label('')
                            ->view('crm::costing.items-notice'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Total Investment')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->placeholder(fn (Get $get, Set $set, ?CostingTemplate $record) => $record ? number_format($record->costingTemplateItems()->sum('total_cost'), 0, ',', '.') : '0'),
                                TextInput::make('total_monthly_cost')
                                    ->label('Total Monthly Cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->placeholder(fn (Get $get, Set $set, ?CostingTemplate $record) => $record ? number_format($record->getTotalMonthlyCost(), 0, ',', '.') : '0'),
                            ]),
                    ]),
            ])->columnSpanFull(),
        ]);
    }
}
