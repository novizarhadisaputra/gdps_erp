<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\Finance\Services\ManpowerCostingService;

class ManpowerTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Costing Identification')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('code')
                                ->label('Costing Code')
                                ->copyable(),
                            TextEntry::make('name')
                                ->label('Costing Name'),
                            TextEntry::make('projectArea.name')
                                ->label('Project Area'),
                            TextEntry::make('contractType.name')
                                ->label('Contract Type'),
                            TextEntry::make('workScheme.name')
                                ->label('Work Scheme'),
                            TextEntry::make('is_active')
                                ->label('Status')
                                ->badge()
                                ->state(fn ($record) => $record->is_active ? 'Active' : 'Inactive')
                                ->color(fn ($record) => $record->is_active ? 'success' : 'danger'),
                        ]),
                    TextEntry::make('description')
                        ->label('Description')
                        ->markdown()
                        ->columnSpanFull(),
                ]),

            Section::make('Personnel Composition & Cost Summary')
                ->schema([
                    TextEntry::make('cost_simulation_table')
                        ->label('Monthly Cost Breakdown')
                        ->html()
                        ->columnSpanFull()
                        ->state(function ($record) {
                            $service = app(ManpowerCostingService::class);
                            $costSimulation = $record->getCostSimulation();

                            $rows = '';
                            foreach ($costSimulation['items'] as $item) {
                                $fmt = fn ($val) => number_format($val, 0, ',', '.');
                                $rows .= "
                                    <tr class='border-b hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors'>
                                        <td class='px-4 py-3 font-medium text-gray-900 dark:text-gray-100'>{$item['job_position_name']}</td>
                                        <td class='px-4 py-3 text-center'>{$item['quantity']}</td>
                                        <td class='px-4 py-3 text-right'>Rp {$fmt($item['unit_direct_cost'])}</td>
                                        <td class='px-4 py-3 text-right font-bold text-primary-600'>Rp {$fmt($item['subtotal_direct_cost'])}</td>
                                    </tr>
                                ";
                            }

                            return new HtmlString("
                                <div class='relative overflow-x-auto shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'>
                                    <table class='w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400'>
                                        <thead class='text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800/50 dark:text-gray-400'>
                                            <tr>
                                                <th scope='col' class='px-4 py-4'>Job Position</th>
                                                <th scope='col' class='px-4 py-4 text-center'>Qty</th>
                                                <th scope='col' class='px-4 py-4 text-right'>Monthly Cost / Person</th>
                                                <th scope='col' class='px-4 py-4 text-right'>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$rows}
                                        </tbody>
                                        <tfoot>
                                            <tr class='font-bold text-gray-900 dark:text-white bg-gray-50/80 dark:bg-gray-800/80'>
                                                <td colspan='2' class='px-4 py-5 text-right uppercase tracking-wider text-xs'>Estimated Monthly Direct Cost</td>
                                                <td colspan='2' class='px-4 py-5 text-right text-xl text-primary-600'>Rp ".number_format($costSimulation['total_direct_cost'], 0, ',', '.')."</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class='mt-4 p-4 rounded-xl bg-blue-50/30 dark:bg-blue-900/10 border border-blue-100/50 dark:border-blue-800/50 flex gap-3 items-start'>
                                    <div class='p-2 bg-blue-100 dark:bg-blue-800 rounded-lg text-blue-600 dark:text-blue-300'>
                                        <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                                    </div>
                                    <div>
                                        <p class='text-xs text-blue-800 dark:text-blue-200 leading-relaxed'>
                                            The figures presented above represent estimated direct manpower costs (Salary + BPJS + Accruals). Actual payroll figures may vary based on attendance, overtime, and specific tax conditions.
                                        </p>
                                    </div>
                                </div>
                            ");
                        }),
                ]),

            Section::make('Metadata')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('created_at')
                                ->dateTime()
                                ->label('Created At'),
                            TextEntry::make('updated_at')
                                ->dateTime()
                                ->label('Last Updated'),
                        ]),
                ])
                ->collapsed(),
        ];
    }
}
