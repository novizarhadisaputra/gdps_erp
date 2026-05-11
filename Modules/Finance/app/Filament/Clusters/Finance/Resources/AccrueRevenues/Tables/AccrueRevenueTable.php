<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Services\AccrualMappingService;

class AccrueRevenueTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (AccrueRevenueStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (AccrueRevenueStatus $state): string => $state->getLabel()),
                TextColumn::make('project.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.number')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_code')
                    ->label('BU')
                    ->toggleable(),
                TextColumn::make('items.workCompletionReport.number')
                    ->label('BAPP')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('items.revenueType.name')
                    ->label('Revenue Segment')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('work_period')
                    ->label('Work Period')
                    ->date('F Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('accrual_period')
                    ->label('Accrual Period')
                    ->date('F Y')
                    ->sortable(),
                TextColumn::make('total_amount_expense_estimated')
                    ->label('Est. Expense')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->summarize(Sum::make()->label('Total Est. Expense')),
                TextColumn::make('total_amount_estimated')
                    ->label('Accrued Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Accrued')),
                TextColumn::make('total_amount_expense_actual')
                    ->label('Act. Expense')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->summarize(Sum::make()->label('Total Act. Expense')),
                TextColumn::make('total_amount_actual')
                    ->label('Invoiced Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Invoiced'))
                    ->weight('bold')
                    ->color(fn ($record) => $record->total_amount_actual >= $record->total_amount_estimated ? 'success' : 'warning'),
            ])
            ->defaultSort('accrual_period', 'desc')
            ->headerActions([
                Action::make('exportAllSap')
                    ->label('Export All to SAP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Table $table) {
                        $query = $table->getQuery();
                        $records = $query->get();
                        $mappingService = app(AccrualMappingService::class);

                        $filename = 'SAP_ALL_EXPORT_'.date('Ymd_His').'.csv';

                        return Response::stream(function () use ($records, $mappingService) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Company Code', 'Period', 'Currency', 'Reference', 'Doc Type', 'Posting Key', 'GL Account', 'Amount', 'Text']);

                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    $amount = $item->amount_estimated;
                                    $period = $record->accrual_period->format('m/Y');

                                    $accrualAccount = $mappingService->resolveAccount('accrual', $record->projectArea, $record->customer, $item->revenue_type_id, $record->project?->revenue_segment_id);
                                    $revenueAccount = $mappingService->resolveAccount('revenue', $record->projectArea, $record->customer, $item->revenue_type_id, $record->project?->revenue_segment_id);

                                    fputcsv($file, [$record->company_code, $period, 'IDR', $record->sap_reference ?? $record->number, 'SA', '40', $accrualAccount ?? 'MISSING_GL', $amount, 'Accrual '.($item->revenueType?->name ?? 'Accrual')]);
                                    fputcsv($file, [$record->company_code, $period, 'IDR', $record->sap_reference ?? $record->number, 'SA', '50', $revenueAccount ?? 'MISSING_GL', $amount, 'Revenue '.($item->revenueType?->name ?? 'Revenue')]);
                                }
                            }
                            fclose($file);
                        }, 200, [
                            'Content-type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=$filename",
                        ]);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadSapBulk')
                        ->label('Export Selected to SAP')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $mappingService = app(AccrualMappingService::class);
                            $filename = 'SAP_BULK_UPLOAD_'.date('Ymd_His').'.csv';

                            $headers = [
                                'Content-type' => 'text/csv',
                                'Content-Disposition' => "attachment; filename=$filename",
                                'Pragma' => 'no-cache',
                                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                                'Expires' => '0',
                            ];

                            $callback = function () use ($records, $mappingService) {
                                $file = fopen('php://output', 'w');
                                fputcsv($file, ['Company Code', 'Period', 'Currency', 'Reference', 'Doc Type', 'Posting Key', 'GL Account', 'Amount', 'Text']);

                                foreach ($records as $record) {
                                    foreach ($record->items as $item) {
                                        $amount = $item->amount_estimated;
                                        $period = $record->accrual_period->format('m/Y');

                                        $accrualAccount = $mappingService->resolveAccount(
                                            'accrual',
                                            $record->projectArea,
                                            $record->customer,
                                            $item->revenue_type_id,
                                            $record->project?->revenue_segment_id
                                        );
                                        $revenueAccount = $mappingService->resolveAccount(
                                            'revenue',
                                            $record->projectArea,
                                            $record->customer,
                                            $item->revenue_type_id,
                                            $record->project?->revenue_segment_id
                                        );

                                        // Row 1: Debit Accrual (40)
                                        fputcsv($file, [
                                            $record->company_code,
                                            $period,
                                            'IDR',
                                            $record->sap_reference ?? $record->number,
                                            'SA',
                                            '40',
                                            $accrualAccount ?? 'MISSING_GL',
                                            $amount,
                                            'Accrual '.($item->revenueType?->name ?? 'Accrual'),
                                        ]);

                                        // Row 2: Credit Revenue (50)
                                        fputcsv($file, [
                                            $record->company_code,
                                            $period,
                                            'IDR',
                                            $record->sap_reference ?? $record->number,
                                            'SA',
                                            '50',
                                            $revenueAccount ?? 'MISSING_GL',
                                            $amount,
                                            'Revenue '.($item->revenueType?->name ?? 'Revenue'),
                                        ]);
                                    }
                                }
                                fclose($file);
                            };

                            return Response::stream($callback, 200, $headers);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                Filter::make('ready_for_sap')
                    ->label('Ready for SAP')
                    ->query(fn ($query) => $query->where('status', AccrueRevenueStatus::Open)->whereNull('sap_reference'))
                    ->indicator('Ready for SAP'),
            ]);
    }
}
