<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->headerActions([
                        Action::make('Edit Step')
                            ->label('Edit')
                            ->icon(Heroicon::OutlinedPencil)
                            ->color('gray')
                            ->size('sm')
                            ->schema(ProfitabilityAnalysisForm::schema(startStep: 1))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit General Information'),
                    ])
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('proposal.proposal_number')
                                    ->label('Proposal Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('workScheme.name')
                                    ->label('Work Scheme'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tor_document')
                                    ->label('ToR Document')
                                    ->state(fn ($record) => $record?->getFirstMedia('tor')?->file_name ?? 'No ToR')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('tor');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No ToR' ? 'gray' : 'primary'),
                                TextEntry::make('rfp_document')
                                    ->label('RFP Document')
                                    ->state(fn ($record) => $record?->getFirstMedia('rfp')?->file_name ?? 'No RFP')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('rfp');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedDocumentChartBar)
                                    ->color(fn ($state) => $state === 'No RFP' ? 'gray' : 'primary'),
                                TextEntry::make('rfq_document')
                                    ->label('RFQ')
                                    ->state(fn ($record) => $record?->getFirstMedia('rfq')?->file_name ?? 'No RFQ')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('rfq');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedInformationCircle)
                                    ->color(fn ($state) => $state === 'No RFQ' ? 'gray' : 'primary'),
                            ]),
                    ]),
                Section::make('Project Parameters')
                    ->headerActions([
                        Action::make('Edit Step')
                            ->label('Edit')
                            ->icon(Heroicon::OutlinedPencil)
                            ->color('gray')
                            ->size('sm')
                            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 2))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit Project Parameters'),
                    ])
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('productCluster.name')
                                    ->label('Product Cluster'),
                                TextEntry::make('tax.name')
                                    ->label('Tax'),
                                TextEntry::make('projectArea.name')
                                    ->label('Project Area'),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                    ]),
                Section::make('Financial Performance')
                    ->headerActions([
                        Action::make('Edit Manpower')
                            ->label('Manpower')
                            ->icon(Heroicon::OutlinedUsers)
                            ->color('gray')
                            ->size('sm')
                            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 3))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit Manpower Costing')
                            ->visible(fn ($record) => $record && ! $record->is_manual_cost),
                        Action::make('Edit Operational')
                            ->label('Operational')
                            ->icon(Heroicon::OutlinedWrenchScrewdriver)
                            ->color('gray')
                            ->size('sm')
                            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 4))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit Operational Costing')
                            ->visible(fn ($record) => $record && ! $record->is_manual_cost),
                        Action::make('Edit Manual')
                            ->label('Manual Costs')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->color('gray')
                            ->size('sm')
                            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 5))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit Manual Cost Breakdown')
                            ->visible(fn ($record) => $record?->is_manual_cost),
                        Action::make('Edit Indirect')
                            ->label('Indirect')
                            ->icon(Heroicon::OutlinedPresentationChartLine)
                            ->color('gray')
                            ->size('sm')
                            ->form(fn () => ProfitabilityAnalysisForm::schema(startStep: 6))
                            ->action(fn ($record, array $data) => $record->update($data))
                            ->modalHeading('Edit Indirect Costing'),
                    ])
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('revenue_per_month')
                                    ->label('1. TOTAL REVENUE (EXCL. PPN)')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('direct_cost')
                                    ->label('2. TOTAL DIRECT COST')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('direct_cost_manpower')
                                    ->label(' - Manpower')
                                    ->state(fn ($record) => $record?->getTotalDirectCostByCategory('manpower') ?? 0)
                                    ->money('IDR'),
                                TextEntry::make('direct_cost_tools')
                                    ->label(' - Tools & Eq')
                                    ->state(fn ($record) => $record?->getTotalDirectCostByCategory('tools_equipment') ?? 0)
                                    ->money('IDR'),
                                TextEntry::make('direct_cost_material')
                                    ->label(' - Material')
                                    ->state(fn ($record) => $record?->getTotalDirectCostByCategory('material') ?? 0)
                                    ->money('IDR'),
                            ])->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('gross_profit')
                                    ->label('3. GROSS PROFIT')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('info'),
                                TextEntry::make('total_indirect_cost')
                                    ->label('4. TOTAL INDIRECT COST')
                                    ->state(fn ($record) => $record?->getTotalIndirectCost() ?? 0)
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('indirect_breakdown')
                                    ->label('Indirect Cost Breakdown')
                                    ->state(function (ProfitabilityAnalysis $record) {
                                        $items = $record->getIndirectItems();
                                        if ($items->isEmpty()) {
                                            return 'No indirect costs defined.';
                                        }

                                        return $items->map(function ($item) use ($record) {
                                            $name = $item->category->name ?? 'Miscellaneous';
                                            $val = (float) ($item->total_monthly_cost ?? $item->unit_cost_price ?? 0);
                                            $formattedVal = number_format($val, 0, ',', '.');

                                            if (($item->calculation_type ?? 'fixed') === 'percentage') {
                                                $basis = $item->percentage_basis ?? 'revenue';
                                                $basisValue = $basis === 'revenue' ? (float) $record->revenue_per_month : (float) $record->direct_cost;
                                                $calc = number_format($basisValue * ($val / 100), 0, ',', '.');

                                                return "• {$name}: {$val}% of {$basis} (IDR {$calc})";
                                            }

                                            return "• {$name}: IDR {$formattedVal}";
                                        })->join("\n");
                                    })
                                    ->prose()
                                    ->listWithLineBreaks(),
                            ])->visible(fn ($record) => $record?->getIndirectItems()->isNotEmpty()),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('ebitda')
                                    ->label('5. EBITDA')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('ebit')
                                    ->label('6. EBIT')
                                    ->money('IDR'),
                                TextEntry::make('ebt')
                                    ->label('7. EBT')
                                    ->money('IDR'),
                                TextEntry::make('net_profit')
                                    ->label('8. NET PROFIT')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('net_profit_margin')
                                    ->label('9. NPM (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ]),
                    ]),
                Section::make('Signatures')
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
