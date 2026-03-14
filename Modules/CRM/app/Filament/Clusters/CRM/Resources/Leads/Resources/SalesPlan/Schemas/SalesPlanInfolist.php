<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class SalesPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Core Information')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('lead.title')
                                    ->label('Lead Title')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('ams.name')
                                    ->label('Account Manager (AMS)')
                                    ->icon(Heroicon::OutlinedUser),
                                TextEntry::make('project_code')
                                    ->label('Project Code')
                                    ->copyable()
                                    ->placeholder('Pending...'),
                            ]),
                    ]),

                Section::make('Service Categorization')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('revenueSegment.name')
                                    ->label('Revenue Segment'),
                                TextEntry::make('productCluster.name')
                                    ->label('Product Cluster'),
                                TextEntry::make('projectType.name')
                                    ->label('Project Type'),
                                TextEntry::make('skillCategory.name')
                                    ->label('Skill Category'),
                                TextEntry::make('industrialSector.name')
                                    ->label('Industrial Sector'),
                                TextEntry::make('projectArea.name')
                                    ->label('Project Area'),
                            ]),
                    ]),

                Section::make('Financials & Timeline')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->date()
                                    ->label('Start Date'),
                                TextEntry::make('end_date')
                                    ->date()
                                    ->label('End Date'),
                                TextEntry::make('cutoff_day')
                                    ->label('Cut-off Day')
                                    ->suffix('th of month'),
                                TextEntry::make('proration_method')
                                    ->badge()
                                    ->label('Proration Method'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('estimated_value')
                                    ->money('IDR')
                                    ->label('Estimated Value')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                TextEntry::make('management_fee_percentage')
                                    ->label('Management Fee')
                                    ->suffix('%'),
                                TextEntry::make('npm_percentage')
                                    ->label('Net Profit Margin (NPM)')
                                    ->suffix('%'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('paymentTerm.name')
                                    ->label('Payment Term'),
                                TextEntry::make('top_days')
                                    ->label('ToP (Days)')
                                    ->suffix(' Days'),
                            ]),
                    ]),

                Section::make('Resources & Distribution')
                    ->icon(Heroicon::OutlinedUsers)
                    ->schema([
                        TextEntry::make('job_positions')
                            ->label('Required Job Positions')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->state(function (Model $record) {
                                $ids = $record->job_positions ?? [];

                                return \Modules\MasterData\Models\JobPosition::whereIn('id', $ids)->pluck('name')->toArray();
                            }),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('revenue_distribution_planning')
                                    ->label('Monthly Revenue Distribution')
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

                Section::make('Governance')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('confidence_level')
                                    ->badge()
                                    ->label('Confidence Level'),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created At'),
                            ]),
                    ]),
            ]);
    }
}
