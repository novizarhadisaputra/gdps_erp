<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\MasterData\Models\JobPosition;

class SalesPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Core Information'))
                    ->icon(Heroicon::OutlinedIdentification)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('lead.title')
                                    ->label(__('Lead Title'))
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('ams.name')
                                    ->label(__('Account Manager (AMS)'))
                                    ->icon(Heroicon::OutlinedUser),
                                TextEntry::make('project_code')
                                    ->label(__('Project Code'))
                                    ->copyable()
                                    ->placeholder(__('Pending...')),
                            ]),
                    ]),

                Section::make(__('Profitability Analysis Status'))
                    ->description(__('Overview of financial feasibility as calculated by the Finance team.'))
                    ->icon(Heroicon::OutlinedPresentationChartBar)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('pa_status')
                                    ->label(__('PA Hub Status'))
                                    ->badge()
                                    ->state(fn (Model $record) => $record->lead?->profitabilityAnalyses()->latest()->first()?->status)
                                    ->formatStateUsing(fn ($state) => $state instanceof HasLabel ? $state->getLabel() : ($state ?? 'No Analysis Linked'))
                                    ->color(fn ($state) => $state instanceof ProfitabilityAnalysisStatus ? $state->getColor() : 'gray')
                                    ->icon(fn ($state) => $state instanceof ProfitabilityAnalysisStatus ? $state->getIcon() : Heroicon::OutlinedQuestionMarkCircle),

                                TextEntry::make('npm_percentage')
                                    ->label(__('Net Profit Margin (NPM)'))
                                    ->suffix('%')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->helperText(__('Approved NPM target.')),

                                TextEntry::make('management_fee_percentage')
                                    ->label(__('Management Fee'))
                                    ->suffix('%')
                                    ->color('info')
                                    ->helperText(__('Agreed management fee.')),
                            ]),
                    ]),

                Section::make(__('Service Categorization'))
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('revenueSegment.name')
                                    ->label(__('Revenue Segment')),
                                TextEntry::make('productCluster.name')
                                    ->label(__('Product Cluster')),
                                TextEntry::make('projectType.name')
                                    ->label(__('Project Type')),
                                TextEntry::make('skillCategory.name')
                                    ->label(__('Skill Category')),
                                TextEntry::make('industrialSector.name')
                                    ->label(__('Industrial Sector')),
                                TextEntry::make('projectArea.name')
                                    ->label(__('Project Area')),
                            ]),
                    ]),

                Section::make(__('Financials & Timeline'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->date()
                                    ->label(__('Start Date')),
                                TextEntry::make('end_date')
                                    ->date()
                                    ->label(__('End Date')),
                                TextEntry::make('cutoff_day')
                                    ->label(__('Cut-off Day'))
                                    ->suffix('th of month'),
                                TextEntry::make('proration_method')
                                    ->badge()
                                    ->label(__('Proration Method')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('estimated_value')
                                    ->money('IDR')
                                    ->label(__('Estimated Value'))
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                TextEntry::make('management_fee_percentage')
                                    ->label(__('Management Fee'))
                                    ->suffix('%'),
                                TextEntry::make('npm_percentage')
                                    ->label(__('Net Profit Margin (NPM)'))
                                    ->suffix('%'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('paymentTerm.name')
                                    ->label(__('Payment Term')),
                                TextEntry::make('top_days')
                                    ->label(__('ToP (Days)'))
                                    ->suffix(' Days'),
                            ]),
                    ]),

                Section::make(__('Resources & Distribution'))
                    ->icon(Heroicon::OutlinedUsers)
                    ->schema([
                        TextEntry::make('job_positions')
                            ->label(__('Required Job Positions'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->state(function (Model $record) {
                                $ids = $record->job_positions ?? [];

                                return JobPosition::whereIn('id', $ids)->pluck('name')->toArray();
                            }),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('revenue_distribution_planning')
                                    ->label(__('Monthly Revenue Distribution'))
                                    ->html()
                                    ->state(function (Model $record) {
                                        $data = $record->revenue_distribution_planning;
                                        if (empty($data)) {
                                            return 'No distribution data available.';
                                        }

                                        $html = '<div class="overflow-x-auto"><table class="w-full text-left border-collapse">';
                                        $html .= '<thead><tr class="border-b border-gray-200 dark:border-white/10"><th class="py-2 pr-4">Month</th><th class="py-2 px-4">Budget</th><th class="py-2 pl-4">Forecast</th></tr></thead>';
                                        $html .= '<tbody>';

                                        foreach ($data as $item) {
                                            $budget = number_format($item['budget_amount'] ?? 0, 0, ',', '.');
                                            $forecast = number_format($item['forecast_amount'] ?? 0, 0, ',', '.');
                                            $html .= "<tr class='border-b border-gray-100 dark:border-white/5'>";
                                            $html .= "<td class='py-2 pr-4 font-medium'>{$item['month']}</td>";
                                            $html .= "<td class='py-2 px-4 text-gray-600 dark:text-gray-400'>IDR {$budget}</td>";
                                            $html .= "<td class='py-2 pl-4 text-gray-600 dark:text-gray-400'>IDR {$forecast}</td>";
                                            $html .= '</tr>';
                                        }

                                        $html .= '</tbody></table></div>';

                                        return $html;
                                    }),
                            ]),
                    ]),

                Section::make(__('Governance'))
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('confidence_level')
                                    ->badge()
                                    ->label(__('Confidence Level')),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label(__('Created At')),
                            ]),
                    ]),

                Section::make(__('Document Tracking'))
                    ->description(__('Reference numbers for documents associated with this lead.'))
                    ->icon(Heroicon::OutlinedTicket)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('proposal_number')
                                    ->label(__('Proposal #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                                TextEntry::make('contract_number')
                                    ->label(__('Contract / PKS #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                                TextEntry::make('po_number')
                                    ->label(__('Purchase Order #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                                TextEntry::make('so_number')
                                    ->label(__('Sales Order #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                                TextEntry::make('wo_number')
                                    ->label(__('Work Order / SPK #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                                TextEntry::make('ba_number')
                                    ->label(__('BAPP / BA #'))
                                    ->copyable()
                                    ->placeholder(__('N/A')),
                            ]),
                    ]),
            ]);
    }
}
