<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostingTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pic.name')
                    ->label(__('PIC'))
                    ->sortable(),
                TextColumn::make('total_monthly_cost')
                    ->label(__('Monthly Cost'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make(__('pdf'))
                    ->label(__('Export PDF'))
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(function ($record) {
                        $pdf = Pdf::loadView('crm::pdf.costing_template', ['record' => $record]);
                        $name = str_replace(['/', '\\'], '-', $record->name);
                        $leadName = \Illuminate\Support\Str::slug($record->lead?->company_name ?? $record->lead?->title ?? 'Unknown-Lead', '-');
                        $fileName = "Costing_{$name}_{$leadName}.pdf";

                        return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                    }),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
