<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;

class EditCostingTemplate extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = Pdf::loadView('crm::pdf.costing_template', ['record' => $record]);
                    $name = Str::slug($record->name, '-');

                    return response()->streamDownload(fn () => print ($pdf->output()), "costing-template-{$name}.pdf");
                }),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
