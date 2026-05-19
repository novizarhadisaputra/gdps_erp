<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ManpowerTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('Positions'))
                    ->suffix(' '.__('Positions')),
                ToggleColumn::make('is_active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                        $costSimulation = $record->getCostSimulation();
                        $pdf = Pdf::loadView('crm::pdf.manpower_template', [
                            'record' => $record,
                            'costSimulation' => $costSimulation,
                        ]);
                        $name = str_replace(['/', '\\'], '-', $record->name);
                        $leadName = \Illuminate\Support\Str::slug($record->lead?->company_name ?? $record->lead?->title ?? 'Unknown-Lead', '-');
                        $fileName = "Manpower_{$name}_{$leadName}.pdf";

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
