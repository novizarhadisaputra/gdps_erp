<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
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
                TextColumn::make('project.code')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_code')
                    ->label('BU')
                    ->toggleable(),
                TextColumn::make('items.bapp.number')
                    ->label('BAPP')
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
                TextColumn::make('total_amount_estimated')
                    ->label('Estimated Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Estimated')),
                TextColumn::make('total_amount_actual')
                    ->label('Actual Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Actual'))
                    ->weight('bold'),
            ])
            ->defaultSort('accrual_period', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('downloadSap')
                        ->label('Download SAP Template')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($record) {
                            $mappingService = app(AccrualMappingService::class);
                            $items = $record->items;

                            $filename = 'SAP_UPLOAD_'.$record->sap_reference.'_'.date('Ymd_His').'.csv';

                            $headers = [
                                'Content-type' => 'text/csv',
                                'Content-Disposition' => "attachment; filename=$filename",
                                'Pragma' => 'no-cache',
                                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                                'Expires' => '0',
                            ];

                            $callback = function () use ($record, $items, $mappingService) {
                                $file = fopen('php://output', 'w');
                                fputcsv($file, ['Company Code', 'Period', 'Currency', 'Reference', 'Doc Type', 'Posting Key', 'GL Account', 'Amount', 'Text']);

                                foreach ($items as $item) {
                                    $amount = $item->amount_estimated;
                                    $period = $record->accrual_period->format('m/Y');

                                    // 1. Resolve Accounts
                                    $accrualAccount = $mappingService->resolveAccount('accrual', $record->projectArea, $record->customer);
                                    $revenueAccount = $mappingService->resolveAccount('revenue', $record->projectArea, $record->customer);

                                    // Row 1: Debit Accrual (PK 40)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->id,
                                        'SA',
                                        '40',
                                        $accrualAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Accrual '.$item->revenue_type->getLabel(),
                                    ]);

                                    // Row 2: Credit Revenue (PK 50)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->id,
                                        'SA',
                                        '50',
                                        $revenueAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Revenue '.$item->revenue_type->getLabel(),
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->stream($callback, 200, $headers);
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
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

                                    $accrualAccount = $mappingService->resolveAccount('accrual', $record->projectArea, $record->customer);
                                    $revenueAccount = $mappingService->resolveAccount('revenue', $record->projectArea, $record->customer);

                                    // Row 1: Debit Accrual (40)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->id,
                                        'SA',
                                        '40',
                                        $accrualAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Accrual '.$item->revenue_type->getLabel(),
                                    ]);

                                    // Row 2: Credit Revenue (50)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->id,
                                        'SA',
                                        '50',
                                        $revenueAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Revenue '.$item->revenue_type->getLabel(),
                                    ]);
                                }
                            }
                            fclose($file);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ])
            ->filters([
                Filter::make('ready_for_sap')
                    ->label('Ready for SAP')
                    ->query(fn ($query) => $query->where('status', AccrueRevenueStatus::Open)->whereNull('sap_reference'))
                    ->indicator('Ready for SAP'),
            ]);
    }
}
