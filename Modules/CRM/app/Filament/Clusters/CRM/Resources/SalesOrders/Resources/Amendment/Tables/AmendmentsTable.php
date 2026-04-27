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
                            $filename = "soa-{$record->salesOrder->number}-rev{$record->number}";
                            $filename = str_replace(['/', '\\'], '-', $filename);

                            return response()->streamDownload(fn () => print ($pdf->output()), "{$filename}.pdf");
                        }),
                ])
                ->icon(Heroicon::EllipsisVertical)
                ->color('gray')
                ->button()
                ->label('Actions'),
            ]);
    }
}
