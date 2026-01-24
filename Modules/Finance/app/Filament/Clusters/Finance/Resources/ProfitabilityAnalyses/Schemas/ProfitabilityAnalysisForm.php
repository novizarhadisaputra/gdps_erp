<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('proposal_id')
                    ->relationship('proposal', 'proposal_number', fn ($query) => $query->where('status', 'approved'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $proposal = \Modules\CRM\Models\Proposal::query()->find($state);
                            if ($proposal) {
                                $set('client_id', $proposal->client_id);
                            }
                        }
                    }),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Section::make('Project Code Parameters')
                    ->schema([
                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('product_cluster_id')
                            ->relationship('productCluster', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('tax_id')
                            ->relationship('tax', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('project_area_id')
                            ->relationship('projectArea', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make('Financial Analysis')
                    ->schema([
                        TextInput::make('revenue_per_month')
                            ->numeric()
                            ->prefix('IDR')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateMargin($state, $get('direct_cost'), $set)),
                        TextInput::make('direct_cost')
                            ->numeric()
                            ->prefix('IDR')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateMargin($get('revenue_per_month'), $state, $set)),
                        TextInput::make('management_fee')
                            ->numeric()
                            ->prefix('IDR'),
                        TextInput::make('margin_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->readOnly()
                            ->placeholder('Auto-calculated'),
                    ])->columns(2),

                Tabs::make('Details')
                    ->tabs([
                        Tab::make('Manpower')
                            ->schema([
                                Repeater::make('manpower_details')
                                    ->schema([
                                        TextInput::make('role')->required(),
                                        TextInput::make('count')->numeric()->default(1),
                                        TextInput::make('salary')->numeric()->prefix('IDR'),
                                    ])->columns(3),
                            ]),
                        Tab::make('Material & Equipment')
                            ->schema([
                                Repeater::make('material_details')
                                    ->schema([
                                        TextInput::make('item')->required(),
                                        TextInput::make('quantity')->numeric()->default(1),
                                        TextInput::make('price')->numeric()->prefix('IDR'),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        if ($revenue > 0) {
            $revenue = (float) $revenue;
            $cost = (float) $cost;
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
    }
}
