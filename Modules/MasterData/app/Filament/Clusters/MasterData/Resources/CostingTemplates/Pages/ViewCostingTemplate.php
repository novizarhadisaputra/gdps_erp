<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\CostingTemplateResource;

class ViewCostingTemplate extends ViewRecord
{
    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $record = $this->getRecord()->load('costingTemplateItems');
                    $pdf = Pdf::loadView('masterdata::pdf.costing_template', ['record' => $record]);
                    $name = Str::slug($record->name, '-');

                    return response()->streamDownload(fn () => print ($pdf->output()), "costing-template-{$name}.pdf");
                }),
            EditAction::make(),
        ];
    }
}
