<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ProjectReviewStatus;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProjectReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review Links')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('lead_id')
                                    ->relationship('lead', 'company_name')
                                    ->required()
                                    ->live()
                                    ->searchable(),
                                Select::make('status')
                                    ->options(ProjectReviewStatus::class)
                                    ->required()
                                    ->default(ProjectReviewStatus::Draft),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('general_information_id')
                                    ->label('General Information')
                                    ->options(fn ($get) => GeneralInformation::where('lead_id', $get('lead_id'))->pluck('number', 'id'))
                                    ->searchable()
                                    ->placeholder('Select GI'),
                                Select::make('profitability_analysis_id')
                                    ->label('Profitability Analysis')
                                    ->options(fn ($get) => ProfitabilityAnalysis::where('lead_id', $get('lead_id'))->pluck('number', 'id'))
                                    ->searchable()
                                    ->placeholder('Select PA'),
                                Select::make('proposal_id')
                                    ->label('Proposal')
                                    ->options(fn ($get) => Proposal::where('lead_id', $get('lead_id'))->pluck('number', 'id'))
                                    ->searchable()
                                    ->placeholder('Select Proposal'),
                            ]),
                    ]),
            ]);
    }
}
