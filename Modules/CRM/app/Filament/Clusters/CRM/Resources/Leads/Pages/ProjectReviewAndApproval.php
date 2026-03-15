<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;

class ProjectReviewAndApproval extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected static ?string $title = 'Project Review & Approval';

    public function getSubheading(): ?string
    {
        return 'Consolidated view of General Information, Risk Register, and Profitability Analysis for project approval.';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Review & Approval')
                    ->tabs([
                        Tabs\Tab::make('General Information & Risk Register')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema(fn ($record) => [
                                TextEntry::make('latestGeneralInformation.rr_document_number')
                                    ->label('Risk Register Document')
                                    ->weight(FontWeight::Bold)
                                    ->visible(fn ($record) => $record->latestGeneralInformation()->exists()),
                                Section::make()
                                    ->schema([
                                        Group::make()
                                            ->relationship('latestGeneralInformation')
                                            ->schema(fn () => GeneralInformationInfolist::configure(Schema::make())->getComponents()),
                                    ])
                                    ->visible(fn ($record) => $record->latestGeneralInformation()->exists()),
                                Section::make()
                                    ->schema([
                                        TextEntry::make('no_gi')
                                            ->label('')
                                            ->state('No General Information found for this lead.')
                                            ->color('gray'),
                                    ])
                                    ->visible(fn ($record) => ! $record->latestGeneralInformation()->exists()),
                            ]),
                        Tabs\Tab::make('Profitability Analysis')
                            ->icon('heroicon-o-chart-bar')
                            ->schema(fn ($record) => [
                                TextEntry::make('latestProfitabilityAnalysis.document_number')
                                    ->label('PA Document')
                                    ->weight(FontWeight::Bold)
                                    ->visible(fn ($record) => $record->latestProfitabilityAnalysis()->exists()),
                                Section::make()
                                    ->schema([
                                        Group::make()
                                            ->relationship('latestProfitabilityAnalysis')
                                            ->schema(fn () => ProfitabilityAnalysisInfolist::configure(Schema::make())->getComponents()),
                                    ])
                                    ->visible(fn ($record) => $record->latestProfitabilityAnalysis()->exists()),
                                Section::make()
                                    ->schema([
                                        TextEntry::make('no_pa')
                                            ->label('')
                                            ->state('No Profitability Analysis found for this lead.')
                                            ->color('gray'),
                                    ])
                                    ->visible(fn ($record) => ! $record->latestProfitabilityAnalysis()->exists()),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
