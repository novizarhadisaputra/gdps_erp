<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsJpConfig;
use Modules\MasterData\Models\ProductCluster;

class ManpowerTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Costing Identification'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('code')
                                ->label(__('Costing Code'))
                                ->copyable(),
                            TextEntry::make('name')
                                ->label(__('Costing Name')),
                            TextEntry::make('lead.projectArea.name')
                                ->label(__('Project Area')),
                            TextEntry::make('contractType.name')
                                ->label(__('Contract Type')),
                            TextEntry::make('workScheme.name')
                                ->label(__('Work Scheme')),
                            TextEntry::make('is_active')
                                ->label(__('Status'))
                                ->badge()
                                ->state(fn ($record) => $record->is_active ? 'Active' : 'Inactive')
                                ->color(fn ($record) => $record->is_active ? 'success' : 'danger'),
                        ]),
                    TextEntry::make('description')
                        ->label(__('Description'))
                        ->markdown()
                        ->columnSpanFull(),
                ]),
            Section::make(__('Metadata'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('created_at')
                                ->dateTime()
                                ->label(__('Created At')),
                            TextEntry::make('updated_at')
                                ->dateTime()
                                ->label(__('Last Updated')),
                        ]),
                ]),
            Section::make(__('Personnel Composition & Cost Summary'))
                ->schema([
                    TextEntry::make('cost_simulation_table')
                        ->label(__('Monthly Cost Breakdown'))
                        ->html()
                        ->columnSpanFull()
                        ->state(function ($record) {
                            $costSimulation = $record->getCostSimulation();
                            $items = $costSimulation['rows'] ?? [];
                            $recordItems = $record->items;

                            if (empty($items)) {
                                return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500"><p class="text-sm">No cost items defined in this template yet.</p></div>');
                            }

                            // Group items by product_cluster_id
                            $groupedItems = [];
                            foreach ($items as $index => $itemData) {
                                $dbItem = $recordItems[$index] ?? null;
                                $clusterId = $dbItem?->product_cluster_id ?? 'none';
                                $groupedItems[$clusterId][] = [
                                    'data' => $itemData,
                                    'dbItem' => $dbItem,
                                ];
                            }

                            $fmt = fn ($val) => number_format($val, 0, ',', '.');
                            $totalTemplateCost = 0;
                            $tableContent = '';

                            foreach ($groupedItems as $clusterId => $clusterGroup) {
                                if ($clusterId !== 'none') {
                                    $clusterName = ProductCluster::find($clusterId)?->name ?? __('Unnamed Cluster');
                                } else {
                                    $clusterName = __('General / No Cluster');
                                }

                                $tableContent .= "
                                    <tr class='bg-gray-100/50 dark:bg-gray-800/80'>
                                        <td colspan='8' class='px-2 py-2 font-bold text-gray-900 dark:text-white uppercase text-[10px]'>
                                            Cluster: {$clusterName}
                                        </td>
                                    </tr>
                                ";

                                foreach ($clusterGroup as $groupItem) {
                                    $itemData = $groupItem['data'];
                                    $dbItem = $groupItem['dbItem'];

                                    $qty = (int) ($itemData['quantity'] ?? $itemData['qty'] ?? 0);
                                    $scale = 1 + ((float) ($dbItem?->future_adjustment_rate ?? 0) / 100);
                                    $unitCost = $itemData['unit_cost'] ?? 0;
                                    $lineTotal = $unitCost * $qty;
                                    $totalTemplateCost += $lineTotal;

                                    $subA = ($itemData['upah'] + $itemData['allowances']['non_fixed']) * $scale;
                                    $subB = ($itemData['accruals']['thr'] + $itemData['accruals']['compensation']) * $scale;
                                    $subC = $itemData['bpjs_total'] * $scale;
                                    $subD = $itemData['pph21']['total'] * $scale;
                                    $subE = $itemData['extra_costs_total'] * $scale;

                                    // Detailed breakdown variables scaled
                                    $scaled_gapok = ($itemData['upah'] - $itemData['allowances']['fixed']) * $scale;
                                    $scaled_fixed = $itemData['allowances']['fixed'] * $scale;
                                    $scaled_non_fixed = $itemData['allowances']['non_fixed'] * $scale;
                                    $scaled_upah = $itemData['upah'] * $scale;

                                    // Accruals
                                    $scaled_thr_basis = $itemData['accruals']['basis'] * $scale;
                                    $scaled_thr = $itemData['accruals']['thr'] * $scale;
                                    $scaled_comp = $itemData['accruals']['compensation'] * $scale;

                                    // Tax
                                    $scaled_bruto = ($itemData['pph21']['bruto'] ?? 0) * $scale;
                                    $scaled_tax = ($itemData['pph21']['total'] ?? 0) * $scale;
                                    $tax_rate_pct = ($itemData['pph21']['rate'] ?? 0.0) * 100;
                                    $tax_method = (bool) ($dbItem?->use_ter_method ?? true) ? 'TER' : 'Progresif Psl 17';
                                    $borne_tax = (bool) ($dbItem?->is_tax_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung Perusahaan)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // JKN Kesehatan
                                    $jkn_base = ($itemData['bpjs_health']['base'] ?? 0) * $scale;
                                    $jkn_employer = ($itemData['bpjs_health']['employer'] ?? 0) * $scale;
                                    $jkn_employee = ($itemData['bpjs_health']['employee'] ?? 0) * $scale;
                                    $jkn_employer_rate_pct = ($itemData['bpjs_health']['employer_rate'] ?? 0) * 100;
                                    $jkn_employee_rate_pct = ($itemData['bpjs_health']['employee_rate'] ?? 0) * 100;
                                    $scaled_jkn_total = ($itemData['bpjs_health']['employer_total'] ?? 0) * $scale;
                                    $borne_jkn = (bool) ($dbItem?->is_employee_jkn_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // JKK Ketenagakerjaan
                                    $jkkConfigId = $dbItem?->bpjs_jkk_config_id;
                                    $jkkConfig = $jkkConfigId ? BpjsJkkConfig::find($jkkConfigId) : null;
                                    $jkk_base = ($itemData['bpjs_employment']['details']['jkk']['base'] ?? 0) * $scale;
                                    $jkk_employer = ($itemData['bpjs_employment']['details']['jkk']['employer'] ?? 0) * $scale;
                                    $jkk_employee = ($itemData['bpjs_employment']['details']['jkk']['employee'] ?? 0) * $scale;
                                    $jkk_total = ($itemData['bpjs_employment']['details']['jkk']['line_total'] ?? 0) * $scale;
                                    $jkk_rate_pct = $jkkConfig && $jkkConfig->has_tier ? 'Tier' : ($jkkConfig ? (float) $jkkConfig->employer_rate * 100 : 0.0);
                                    $jkk_employee_rate_pct = $jkkConfig ? (float) $jkkConfig->employee_rate * 100 : 0.0;
                                    $borne_jkk = (bool) ($dbItem?->is_employee_jkk_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // JKM
                                    $jkmConfigId = $dbItem?->bpjs_jkm_config_id;
                                    $jkmConfig = $jkmConfigId ? BpjsJkmConfig::find($jkmConfigId) : null;
                                    $jkm_base = ($itemData['bpjs_employment']['details']['jkm']['base'] ?? 0) * $scale;
                                    $jkm_employer = ($itemData['bpjs_employment']['details']['jkm']['employer'] ?? 0) * $scale;
                                    $jkm_employee = ($itemData['bpjs_employment']['details']['jkm']['employee'] ?? 0) * $scale;
                                    $jkm_total = ($itemData['bpjs_employment']['details']['jkm']['line_total'] ?? 0) * $scale;
                                    $jkm_rate_pct = $jkmConfig ? (float) $jkmConfig->employer_rate * 100 : 0.0;
                                    $jkm_employee_rate_pct = $jkmConfig ? (float) $jkmConfig->employee_rate * 100 : 0.0;
                                    $borne_jkm = (bool) ($dbItem?->is_employee_jkm_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // JHT
                                    $jhtConfigId = $dbItem?->bpjs_jht_config_id;
                                    $jhtConfig = $jhtConfigId ? BpjsJhtConfig::find($jhtConfigId) : null;
                                    $jht_base = ($itemData['bpjs_employment']['details']['jht']['base'] ?? 0) * $scale;
                                    $jht_employer = ($itemData['bpjs_employment']['details']['jht']['employer'] ?? 0) * $scale;
                                    $jht_employee = ($itemData['bpjs_employment']['details']['jht']['employee'] ?? 0) * $scale;
                                    $jht_total = ($itemData['bpjs_employment']['details']['jht']['line_total'] ?? 0) * $scale;
                                    $jht_employer_rate_pct = $jhtConfig && $jhtConfig->has_tier ? 'Tier' : ($jhtConfig ? (float) $jhtConfig->employer_rate * 100 : 0.0);
                                    $jht_employee_rate_pct = $jhtConfig ? (float) $jhtConfig->employee_rate * 100 : 0.0;
                                    $borne_jht = (bool) ($dbItem?->is_employee_jht_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // JP
                                    $jpConfigId = $dbItem?->bpjs_jp_config_id;
                                    $jpConfig = $jpConfigId ? BpjsJpConfig::find($jpConfigId) : null;
                                    $jp_base = ($itemData['bpjs_employment']['details']['jp']['base'] ?? 0) * $scale;
                                    $jp_employer = ($itemData['bpjs_employment']['details']['jp']['employer'] ?? 0) * $scale;
                                    $jp_employee = ($itemData['bpjs_employment']['details']['jp']['employee'] ?? 0) * $scale;
                                    $jp_total = ($itemData['bpjs_employment']['details']['jp']['line_total'] ?? 0) * $scale;
                                    $jp_employer_rate_pct = $jpConfig ? (float) $jpConfig->employer_rate * 100 : 0.0;
                                    $jp_employee_rate_pct = $jpConfig ? (float) $jpConfig->employee_rate * 100 : 0.0;
                                    $borne_jp = (bool) ($dbItem?->is_employee_jp_borne_by_company ?? false)
                                        ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                        : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                    // Extra Costs List
                                    $extra_costs_html = '';
                                    if (! empty($itemData['extra_costs'])) {
                                        $extraRows = '';
                                        foreach ($itemData['extra_costs'] as $ec) {
                                            $ecName = $ec['name'] ?? __('Unnamed Cost');
                                            $ecVal = (float) ($ec['value'] ?? $ec['amount'] ?? 0) * $scale;
                                            $extraRows .= "
                                                <tr class='border-b border-gray-200 dark:border-gray-700/50 text-[9px]'>
                                                    <td class='py-1 text-gray-500 dark:text-gray-400'>{$ecName}</td>
                                                    <td class='text-right font-medium'>Rp {$fmt($ecVal)}</td>
                                                </tr>
                                            ";
                                        }
                                        $extra_costs_html = "
                                            <div class='pt-2 border-t border-gray-200 dark:border-gray-700'>
                                                <div class='font-bold text-gray-900 dark:text-white uppercase text-[9px] mb-1 tracking-wider'>5. Biaya Ekstra (Extra Costs)</div>
                                                <table class='w-full text-[10px]'>
                                                    <tbody>
                                                        {$extraRows}
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class='font-semibold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/30'>
                                                            <td class='py-1.5'>Total Biaya Ekstra</td>
                                                            <td class='text-right py-1.5'>Rp {$fmt($subE)}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        ";
                                    }

                                    $jpName = $itemData['job_position_name'] ?? 'Unnamed';
                                    $jpCode = $itemData['job_position_code'] ?? 'N/A';

                                    $tableContent .= "
                                        <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-xs align-middle'>
                                            <td class='px-2 py-3'>
                                                <div class='font-semibold text-gray-900 dark:text-gray-100'>{$jpName}</div>
                                                <div class='text-[9px] text-gray-400'>{$jpCode} | PTKP: ".($itemData['ptkp_status'] ?? 'TK/0')."</div>
                                            </td>
                                            <td class='px-2 py-3 text-center font-bold'>{$qty}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subA)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subB)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subC)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subD)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subE)}</td>
                                            <td class='px-2 py-3 text-right font-bold text-primary-600'>Rp {$fmt($unitCost)}</td>
                                        </tr>
                                    ";
                                    // Extra Costs Rows inside the spreadsheet
                                    $extra_costs_rows_html = '';
                                    if (! empty($itemData['extra_costs'])) {
                                        $ecIndex = 1;
                                        $extra_costs_rows_html .= "
                                                <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                    <td class='py-1.5 px-3 text-center'>5</td>
                                                    <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>5. BIAYA EKSTRA (EXTRA COSTS)</td>
                                                    <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subE)}</td>
                                                    <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subE * $qty)}</td>
                                                </tr>
                                            ";
                                        foreach ($itemData['extra_costs'] as $ec) {
                                            $ecName = $ec['name'] ?? __('Unnamed Cost');
                                            $ecVal = (float) ($ec['value'] ?? $ec['amount'] ?? 0) * $scale;
                                            $extra_costs_rows_html .= "
                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                        <td class='py-1.5 px-3 text-center text-slate-400'>5.{$ecIndex}</td>
                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>{$ecName}</td>
                                                        <td class='py-1.5 px-3'>Input Biaya Ekstra</td>
                                                        <td class='py-1.5 px-3'>-</td>
                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($ecVal)}</td>
                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($ecVal * $qty)}</td>
                                                    </tr>
                                                ";
                                            $ecIndex++;
                                        }
                                    }

                                    $tableContent .= "
                                            <tr class='bg-gray-50/30 dark:bg-gray-900/10 border-b'>
                                                <td colspan='8' class='px-4 py-2.5'>
                                                    <details class='group select-none'>
                                                        <summary class='cursor-pointer text-[10px] font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 flex items-center gap-1 focus:outline-none py-1 select-none'>
                                                             <svg class='w-3.5 h-3.5 shrink-0' width='14' height='14' style='width: 14px; height: 14px;' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path></svg>
                                                             <span>Lihat Rincian Rumus (Spreadsheet) - {$jpName}</span>
                                                        </summary>
                                                        <div class='mt-2.5 relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900'>
                                                            <table class='w-full text-[10px] text-left text-slate-600 dark:text-slate-400 border-collapse'>
                                                                <thead>
                                                                    <tr class='bg-slate-100 dark:bg-slate-800 text-[9px] uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 font-bold'>
                                                                        <th class='py-2 px-3 text-center w-10'>No.</th>
                                                                        <th class='py-2 px-3'>Komponen Biaya (Cost Component)</th>
                                                                        <th class='py-2 px-3'>Dasar Perhitungan (Calculation Base)</th>
                                                                        <th class='py-2 px-3'>Formula / Rate</th>
                                                                        <th class='py-2 px-3 text-right'>Bagian Perusahaan (Employer)</th>
                                                                        <th class='py-2 px-3 text-right'>Bagian Karyawan (Employee)</th>
                                                                        <th class='py-2 px-3 text-right w-32'>Subtotal / Pax</th>
                                                                        <th class='py-2 px-3 text-right w-32'>Total Cost (Qty: {$qty})</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class='divide-y divide-slate-100 dark:divide-slate-800'>
                                                                    <!-- 1. Dasar Upah -->
                                                                    <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                        <td class='py-1.5 px-3 text-center'>1</td>
                                                                        <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>1. DASAR UPAH & TUNJANGAN (WAGES & ALLOWANCES)</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subA)}</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subA * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>1.1</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Gaji Pokok (Basic Salary)</td>
                                                                        <td class='py-1.5 px-3'>Input Gaji Pokok</td>
                                                                        <td class='py-1.5 px-3'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_gapok)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_gapok * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>1.2</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Tunjangan Tetap (Fixed Allowance)</td>
                                                                        <td class='py-1.5 px-3'>Input Tunjangan Tetap</td>
                                                                        <td class='py-1.5 px-3'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_fixed)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_fixed * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>1.3</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Tunjangan Tidak Tetap (Non-Fixed Allowance)</td>
                                                                        <td class='py-1.5 px-3'>Input Tunjangan Tidak Tetap</td>
                                                                        <td class='py-1.5 px-3'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_non_fixed)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_non_fixed * $qty)}</td>
                                                                    </tr>

                                                                    <!-- 2. Akrual Bulanan -->
                                                                    <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                        <td class='py-1.5 px-3 text-center'>2</td>
                                                                        <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>2. AKRUAL BULANAN (MONTHLY ACCRUALS)</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subB)}</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subB * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>2.1</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Akrual THR (Religious Festive Allowance)</td>
                                                                        <td class='py-1.5 px-3'>Dasar THR: Rp {$fmt($scaled_thr_basis)}</td>
                                                                        <td class='py-1.5 px-3'>1 / 12</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_thr)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_thr * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>2.2</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Akrual Kompensasi (Contract Compensation)</td>
                                                                        <td class='py-1.5 px-3'>Dasar Komp: Rp {$fmt($scaled_thr_basis)}</td>
                                                                        <td class='py-1.5 px-3'>1 / 12</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_comp)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_comp * $qty)}</td>
                                                                    </tr>

                                                                    <!-- 3. BPJS Contributions -->
                                                                    <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                        <td class='py-1.5 px-3 text-center'>3</td>
                                                                        <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>3. IURAN BPJS (BPJS CONTRIBUTIONS)</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subC)}</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subC * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>3.1</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Kesehatan (JKN)</td>
                                                                        <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkn_base)}</td>
                                                                        <td class='py-1.5 px-3'>Perusahaan: {$jkn_employer_rate_pct}%, Karyawan: {$jkn_employee_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkn_employer)}</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkn_employee)} {$borne_jkn}</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_jkn_total)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_jkn_total * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>3.2</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JKK</td>
                                                                        <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkk_base)}</td>
                                                                        <td class='py-1.5 px-3'>Perusahaan: {$jkk_rate_pct}%, Karyawan: {$jkk_employee_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkk_employer)}</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkk_employee)} {$borne_jkk}</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jkk_total)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($jkk_total * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>3.3</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JKM</td>
                                                                        <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkm_base)}</td>
                                                                        <td class='py-1.5 px-3'>Perusahaan: {$jkm_rate_pct}%, Karyawan: {$jkm_employee_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkm_employer)}</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkm_employee)} {$borne_jkm}</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jkm_total)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($jkm_total * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>3.4</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JHT</td>
                                                                        <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jht_base)}</td>
                                                                        <td class='py-1.5 px-3'>Perusahaan: {$jht_employer_rate_pct}%, Karyawan: {$jht_employee_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jht_employer)}</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jht_employee)} {$borne_jht}</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jht_total)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($jht_total * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>3.5</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JP</td>
                                                                        <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jp_base)}</td>
                                                                        <td class='py-1.5 px-3'>Perusahaan: {$jp_employer_rate_pct}%, Karyawan: {$jp_employee_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jp_employer)}</td>
                                                                        <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jp_employee)} {$borne_jp}</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jp_total)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($jp_total * $qty)}</td>
                                                                    </tr>

                                                                    <!-- 4. Pajak PPh 21 -->
                                                                    <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                        <td class='py-1.5 px-3 text-center'>4</td>
                                                                        <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>4. PAJAK PPH 21 (INCOME TAX)</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subD)}</td>
                                                                        <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subD * $qty)}</td>
                                                                    </tr>
                                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                        <td class='py-1.5 px-3 text-center text-slate-400'>4.1</td>
                                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Pajak PPh 21 Bulanan</td>
                                                                        <td class='py-1.5 px-3'>Bruto: Rp {$fmt($scaled_bruto)} | Status: {$borne_tax}</td>
                                                                        <td class='py-1.5 px-3'>Metode: {$tax_method} | Tarif: {$tax_rate_pct}%</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_tax)}</td>
                                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_tax * $qty)}</td>
                                                                    </tr>

                                                                    <!-- 5. Biaya Ekstra -->
                                                                    {$extra_costs_rows_html}

                                                                    <!-- Grand Total -->
                                                                    <tr class='bg-primary-50 dark:bg-primary-950/20 text-slate-900 dark:text-white border-t-2 border-primary-500 font-bold'>
                                                                        <td class='py-2 px-3 text-center'>TOTAL</td>
                                                                        <td colspan='5' class='py-2 px-3 font-bold text-primary-700 dark:text-primary-400 uppercase tracking-wider text-[10px]'>TOTAL DIRECT MANPOWER COST</td>
                                                                        <td class='py-2 px-3 text-right text-sm text-primary-700 dark:text-primary-400'>Rp {$fmt($unitCost)}</td>
                                                                        <td class='py-2 px-3 text-right text-sm text-primary-700 dark:text-primary-400'>Rp {$fmt($lineTotal)}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </details>
                                                </td>
                                            </tr>
                                        ";
                                }
                            }

                            return new HtmlString("
                                <div class='relative overflow-x-auto shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'>
                                    <table class='w-full text-[11px] text-left text-gray-500 dark:text-gray-400'>
                                        <thead class='text-[10px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700'>
                                            <tr>
                                                <th scope='col' class='px-2 py-3'>".__('Position')."</th>
                                                <th scope='col' class='px-2 py-3 text-center'>".__('Qty')."</th>
                                                <th scope='col' class='px-2 py-3 text-right'>".__('Wage')."</th>
                                                <th scope='col' class='px-2 py-3 text-right'>".__('Accruals')."</th>
                                                <th scope='col' class='px-2 py-3 text-right'>".__('BPJS')."</th>
                                                <th scope='col' class='px-2 py-3 text-right'>".__('PPh 21')."</th>
                                                <th scope='col' class='px-2 py-3 text-right'>".__('Extra')."</th>
                                                <th scope='col' class='px-2 py-3 text-right bg-blue-50/50 dark:bg-blue-900/10 text-primary-600'>".__('Total/Pax')."</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$tableContent}
                                        </tbody>
                                        <tfoot>
                                            <tr class='font-bold text-gray-900 dark:text-white bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700'>
                                                <td colspan='7' class='px-4 py-5 text-right uppercase tracking-wider text-[10px]'>Estimated Monthly Direct Cost</td>
                                                <td class='px-4 py-5 text-right text-base text-primary-600 bg-blue-50/30 dark:bg-blue-900/10 font-bold'>Rp {$fmt($totalTemplateCost)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class='mt-4 p-4 rounded-xl bg-blue-50/30 dark:bg-blue-900/10 border border-blue-100/50 dark:border-blue-800/50 flex gap-3 items-start'>
                                    <div class='p-2 bg-blue-100 dark:bg-blue-800 rounded-lg text-blue-600 dark:text-blue-300'>
                                        <svg class='w-5 h-5 shrink-0' width='20' height='20' style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                                    </div>
                                    <div>
                                        <p class='text-[10px] text-blue-800 dark:text-blue-200 leading-relaxed'>
                                            Angka yang disajikan di atas merupakan estimasi biaya tenaga kerja langsung (Upah + BPJS + Akrual). Angka penggajian riil dapat bervariasi berdasarkan kehadiran, lembur, dan kondisi pajak tertentu.
                                        </p>
                                    </div>
                                </div>
                            ");
                        }),
                ])->columnSpanFull(),

        ];
    }
}
