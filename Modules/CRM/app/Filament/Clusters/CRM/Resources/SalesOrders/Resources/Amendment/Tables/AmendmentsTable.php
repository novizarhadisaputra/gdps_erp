<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Models\SalesOrderAmendment;

class AmendmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Amendment Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(50),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Amendment Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('pdf')
                        ->label('Export PDF')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('gray')
                        ->action(function (SalesOrderAmendment $record) {
                            $pdf = Pdf::loadView('crm::pdf.sales-order-amendment', ['record' => $record]);
                            $name = str_replace(['/', '\\'], '-', $record->number);
                            $soName = str_replace(['/', '\\'], '-', $record->salesOrder?->number ?? 'Unknown-SO');
                            $leadName = \Illuminate\Support\Str::slug($record->salesOrder?->lead?->company_name ?? $record->salesOrder?->lead?->title ?? 'Unknown-Lead', '-');
                            $fileName = "SOA_{$name}_{$soName}_{$leadName}.pdf";

                            return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                        }),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->color('gray')
                    ->button()
                    ->label('Actions'),
            ]);
    }
}
